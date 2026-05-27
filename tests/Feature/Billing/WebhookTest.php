<?php

declare(strict_types=1);

use App\Domains\Billing\Enums\SubscriptionStatus;
use App\Domains\Billing\Models\Plan;
use App\Domains\Billing\Models\Subscription;
use App\Models\Team;

test('billing webhook rejects invalid signature', function () {
    config(['services.asaas.webhook_secret' => 'super-secret']);

    $payload = [
        'event' => 'PAYMENT_RECEIVED',
        'subscription' => 'sub_123',
    ];

    $this->postJson(route('billing.webhook'), $payload, [
        'Asaas-Signature' => 'invalid-signature',
    ])->assertStatus(400);
});

test('billing webhook processes valid signature', function () {
    config(['services.asaas.webhook_secret' => 'super-secret']);

    $team = Team::factory()->create([
        'name' => 'Billing Team',
        'slug' => 'billing-team',
    ]);
    $plan = Plan::query()->create([
        'name' => 'Starter',
        'slug' => 'starter',
        'description' => 'Plano inicial',
        'price_monthly' => 29.90,
        'price_yearly' => 299.90,
        'currency' => 'BRL',
        'is_active' => true,
    ]);
    $subscription = Subscription::query()->create([
        'team_id' => $team->id,
        'plan_id' => $plan->id,
        'gateway_subscription_id' => 'sub_123',
        'status' => SubscriptionStatus::Pending,
        'billing_period' => 'monthly',
        'amount' => 29.90,
        'currency' => 'BRL',
        'payment_method' => 'pix',
    ]);

    $payload = [
        'event' => 'PAYMENT_RECEIVED',
        'subscription' => 'sub_123',
    ];
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $signature = hash_hmac('sha256', $body, 'super-secret');

    $response = $this->postJson(route('billing.webhook'), $payload, [
        'Asaas-Signature' => $signature,
    ]);

    $response->assertOk();

    expect($subscription->refresh()->status)->toBe(SubscriptionStatus::Active);
});
