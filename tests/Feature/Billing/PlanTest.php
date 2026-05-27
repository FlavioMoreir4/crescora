<?php

declare(strict_types=1);

use App\Domains\Billing\Models\Plan;

test('plan list is accessible', function () {
    $plan1 = Plan::create([
        'name' => 'Basic',
        'slug' => 'basic',
        'price_monthly' => 29.90,
        'price_yearly' => 299.90,
        'currency' => 'BRL',
        'is_active' => true,
    ]);

    $plan2 = Plan::create([
        'name' => 'Pro',
        'slug' => 'pro',
        'price_monthly' => 99.90,
        'price_yearly' => 999.90,
        'currency' => 'BRL',
        'is_active' => true,
    ]);

    $plans = Plan::where('is_active', true)->get();

    expect($plans)->toHaveCount(2);
    expect($plans->first()->name)->toBe('Basic');
    expect($plans->last()->name)->toBe('Pro');
});
