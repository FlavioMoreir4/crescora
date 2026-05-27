<?php

declare(strict_types=1);

namespace App\Domains\Billing\Contracts;

use App\Domains\Billing\Models\Subscription;
use App\Models\Team;

interface BillingGatewayInterface
{
    /**
     * Create a customer in the billing gateway.
     */
    public function createCustomer(Team $team): string;

    /**
     * Create a subscription for a team.
     */
    public function createSubscription(Subscription $subscription): Subscription;

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(Subscription $subscription): Subscription;

    /**
     * Generate a PIX payment for subscription.
     */
    public function generatePixPayment(Subscription $subscription): array;

    /**
     * Retrieve the invoice URL for a subscription.
     */
    public function getInvoiceUrl(Subscription $subscription): ?string;

    /**
     * Process a webhook event from the gateway.
     */
    public function handleWebhook(array $payload): array;

    /**
     * Check if the gateway is operational.
     */
    public function health(): bool;
}
