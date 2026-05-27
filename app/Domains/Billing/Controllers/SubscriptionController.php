<?php

declare(strict_types=1);

namespace App\Domains\Billing\Controllers;

use App\Domains\Billing\Contracts\BillingGatewayInterface;
use App\Domains\Billing\Enums\SubscriptionStatus;
use App\Domains\Billing\Models\Plan;
use App\Domains\Billing\Models\Subscription;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class SubscriptionController
{
    public function __construct(
        private readonly BillingGatewayInterface $gateway,
    ) {}

    public function index(): Response
    {
        $team = request()->user()->currentTeam;
        $subscription = $team->subscription;

        $plans = Plan::query()->where('is_active', true)->get();

        return Inertia::render('billing/Index', [
            'subscription' => $subscription?->load('plan'),
            'plans' => $plans,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'billing_period' => ['required', 'in:monthly,yearly'],
            'payment_method' => ['required', 'in:pix,credit_card,boleto'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $team = $request->user()->currentTeam;

        if ($team->subscription?->isActive()) {
            throw ValidationException::withMessages([
                'plan_id' => 'O time já possui uma assinatura ativa.',
            ]);
        }

        $amount = $validated['billing_period'] === 'yearly'
            ? $plan->price_yearly
            : $plan->price_monthly;

        $gatewayCustomerId = $this->resolveCustomerId($team);

        $subscription = Subscription::query()->create([
            'team_id' => $team->id,
            'plan_id' => $plan->id,
            'gateway_customer_id' => $gatewayCustomerId,
            'status' => SubscriptionStatus::Pending,
            'billing_period' => $validated['billing_period'],
            'amount' => $amount,
            'payment_method' => $validated['payment_method'],
        ]);

        $this->gateway->createSubscription($subscription);

        return to_route('billing.index');
    }

    public function cancel(): RedirectResponse
    {
        $subscription = request()->user()->currentTeam->subscription;

        abort_unless($subscription !== null, 404);

        $this->gateway->cancelSubscription($subscription);

        return to_route('billing.index');
    }

    public function pixPayment(): JsonResponse
    {
        $subscription = request()->user()->currentTeam->subscription;

        abort_unless($subscription !== null, 404);

        $pix = $this->gateway->generatePixPayment($subscription);

        return response()->json($pix);
    }

    public function webhook(Request $request): JsonResponse
    {
        $this->verifyWebhookSignature($request);

        $result = $this->gateway->handleWebhook($request->all());

        return response()->json($result);
    }

    private function verifyWebhookSignature(Request $request): void
    {
        $secret = config('services.asaas.webhook_secret');

        if ($secret === null || $secret === '') {
            return;
        }

        $signature = $request->header('asaas-signature');

        if ($signature === null) {
            abort(400, 'Missing Asaas signature header.');
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            abort(400, 'Invalid Asaas webhook signature.');
        }
    }

    private function resolveCustomerId(Team $team): string
    {
        if ($team->gateway_customer_id !== null) {
            return $team->gateway_customer_id;
        }

        $id = $this->gateway->createCustomer($team);

        $team->update(['gateway_customer_id' => $id]);

        return $id;
    }
}
