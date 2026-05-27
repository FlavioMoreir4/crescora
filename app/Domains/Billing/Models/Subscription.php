<?php

declare(strict_types=1);

namespace App\Domains\Billing\Models;

use App\Domains\Billing\Enums\SubscriptionStatus;
use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Subscription extends Model
{
    protected $fillable = [
        'team_id',
        'plan_id',
        'gateway_subscription_id',
        'gateway_customer_id',
        'status',
        'billing_period',
        'amount',
        'currency',
        'payment_method',
        'trial_ends_at',
        'current_period_ends_at',
        'canceled_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'amount' => 'decimal:2',
            'trial_ends_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'canceled_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active
            || $this->status === SubscriptionStatus::Trialing;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing]);
    }
}
