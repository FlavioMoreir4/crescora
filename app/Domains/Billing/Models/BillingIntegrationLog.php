<?php

declare(strict_types=1);

namespace App\Domains\Billing\Models;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BillingIntegrationLog extends Model
{
    protected $fillable = [
        'team_id',
        'gateway',
        'event_type',
        'status',
        'request_payload',
        'response_payload',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
