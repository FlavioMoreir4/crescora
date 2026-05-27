<?php

declare(strict_types=1);

namespace App\Domains\Billing\Gateways;

use App\Domains\Billing\Contracts\BillingGatewayInterface;
use App\Domains\Billing\Models\BillingIntegrationLog;
use App\Domains\Billing\Models\Subscription;
use App\Models\Team;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AsaasGateway implements BillingGatewayInterface
{
    private const string BASE_URL_PROD = 'https://api.asaas.com/v3';

    private const string BASE_URL_SANDBOX = 'https://sandbox.asaas.com/api/v3';

    private readonly string $apiKey;

    private readonly string $baseUrl;

    public function __construct(
        private readonly bool $sandbox = true,
    ) {
        $this->apiKey = $sandbox
            ? config('services.asaas.sandbox_key', '')
            : config('services.asaas.prod_key', '');
        $this->baseUrl = $sandbox ? self::BASE_URL_SANDBOX : self::BASE_URL_PROD;
    }

    public function createCustomer(Team $team): string
    {
        $response = $this->client()->post('/customers', [
            'name' => $team->name,
            'email' => $team->owner?->email,
            'phone' => $team->owner?->phone,
            'cpfCnpj' => $team->owner?->document,
            'notificationDisabled' => false,
        ]);

        $this->log('create_customer', $response->successful(), $response->json());

        if ($response->failed()) {
            throw new \RuntimeException('Asaas: failed to create customer: '.($response->json('errors.0.description') ?? 'unknown'));
        }

        return $response->json('id');
    }

    public function createSubscription(Subscription $subscription): Subscription
    {
        $response = $this->client()->post('/subscriptions', [
            'customer' => $subscription->gateway_customer_id,
            'billingType' => match ($subscription->payment_method) {
                'pix' => 'PIX',
                'credit_card' => 'CREDIT_CARD',
                'boleto' => 'BOLETO',
                default => 'PIX',
            },
            'value' => (float) $subscription->amount,
            'nextDueDate' => now()->addDay()->format('Y-m-d'),
            'cycle' => $subscription->billing_period === 'yearly' ? 'YEARLY' : 'MONTHLY',
            'description' => $subscription->plan?->name ?? 'Assinatura',
        ]);

        $this->log('create_subscription', $response->successful(), $response->json());

        if ($response->failed()) {
            throw new \RuntimeException('Asaas: failed to create subscription: '.($response->json('errors.0.description') ?? 'unknown'));
        }

        $subscription->update([
            'gateway_subscription_id' => $response->json('id'),
            'status' => $this->mapAsaasStatus($response->json('status')),
            'current_period_ends_at' => $response->json('nextDueDate')
                ? now()->parse($response->json('nextDueDate'))
                : null,
        ]);

        return $subscription->fresh();
    }

    public function cancelSubscription(Subscription $subscription): Subscription
    {
        $id = $subscription->gateway_subscription_id;

        if ($id === null) {
            $subscription->update(['status' => 'canceled', 'canceled_at' => now()]);

            return $subscription->fresh();
        }

        $response = $this->client()->delete("/subscriptions/{$id}");

        $this->log('cancel_subscription', $response->successful(), $response->json());

        if ($response->failed()) {
            throw new \RuntimeException('Asaas: failed to cancel subscription: '.($response->json('errors.0.description') ?? 'unknown'));
        }

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'ended_at' => now(),
        ]);

        return $subscription->fresh();
    }

    public function generatePixPayment(Subscription $subscription): array
    {
        $id = $subscription->gateway_subscription_id;

        if ($id === null) {
            return [];
        }

        $response = $this->client()->get("/subscriptions/{$id}/pix");

        $this->log('generate_pix', $response->successful(), $response->json());

        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }

    public function getInvoiceUrl(Subscription $subscription): ?string
    {
        $id = $subscription->gateway_subscription_id;

        if ($id === null) {
            return null;
        }

        return "{$this->baseUrl}/subscriptions/{$id}/invoice";
    }

    public function handleWebhook(array $payload): array
    {
        $event = $payload['event'] ?? null;
        $subscriptionId = $payload['subscription'] ?? null;

        if ($event === null || $subscriptionId === null) {
            return ['handled' => false, 'reason' => 'missing_event_or_subscription'];
        }

        $subscription = Subscription::query()
            ->where('gateway_subscription_id', $subscriptionId)
            ->first();

        if ($subscription === null) {
            $this->log('webhook_unmatched', true, $payload);

            return ['handled' => false, 'reason' => 'subscription_not_found'];
        }

        $newStatus = match ($event) {
            'PAYMENT_RECEIVED' => 'active',
            'PAYMENT_OVERDUE' => 'past_due',
            'PAYMENT_CONFIRMED' => 'active',
            'SUBSCRIPTION_CANCELED' => 'canceled',
            'SUBSCRIPTION_EXPIRED' => 'expired',
            default => null,
        };

        if ($newStatus !== null) {
            $subscription->update(['status' => $newStatus]);

            if ($newStatus === 'canceled' || $newStatus === 'expired') {
                $subscription->update(['ended_at' => now()]);
            }
        }

        $this->log("webhook_{$event}", true, $payload);

        return ['handled' => true, 'new_status' => $newStatus];
    }

    public function health(): bool
    {
        try {
            $response = $this->client()->get('/customers', ['limit' => 1]);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeader('access_token', $this->apiKey)
            ->acceptJson()
            ->throw();
    }

    private function log(string $event, bool $success, ?array $payload, ?\Throwable $error = null): void
    {
        try {
            BillingIntegrationLog::query()->create([
                'gateway' => 'asaas',
                'event_type' => $event,
                'status' => $success ? 'success' : 'failed',
                'response_payload' => $payload,
                'error_message' => $error?->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log billing integration', ['error' => $e->getMessage()]);
        }
    }

    private function mapAsaasStatus(string $asaasStatus): string
    {
        return match ($asaasStatus) {
            'ACTIVE' => 'active',
            'INACTIVE' => 'canceled',
            'EXPIRED' => 'expired',
            'PENDING' => 'pending',
            default => 'pending',
        };
    }
}
