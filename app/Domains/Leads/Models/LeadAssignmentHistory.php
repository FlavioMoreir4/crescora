<?php

declare(strict_types=1);

namespace App\Domains\Leads\Models;

use App\Domains\Shared\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadAssignmentHistory extends BaseModel
{
    protected $fillable = [
        'lead_id',
        'from_owner_id',
        'to_owner_id',
        'actor_id',
        'source',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function fromOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_owner_id');
    }

    public function toOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_owner_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
