<?php

declare(strict_types=1);

namespace App\Domains\Leads\Models;

use App\Domains\Leads\Enums\LeadStatus;
use App\Domains\Shared\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStatusHistory extends BaseModel
{
    protected $fillable = [
        'lead_id',
        'from_status',
        'to_status',
        'notes',
        'actor_id',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => LeadStatus::class,
            'to_status' => LeadStatus::class,
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
