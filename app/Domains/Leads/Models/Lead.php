<?php

declare(strict_types=1);

namespace App\Domains\Leads\Models;

use App\Domains\Leads\Enums\LeadStatus;
use App\Domains\Leads\Models\LeadAssignmentHistory;
use App\Domains\Shared\Models\BaseModel;
use App\Domains\Shared\Models\Concerns\BelongsToTeam;
use App\Domains\Units\Models\Unit;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends BaseModel
{
    use BelongsToTeam, SoftDeletes;

    protected $fillable = [
        'team_id',
        'unit_id',
        'owner_id',
        'status',
        'name',
        'email',
        'phone',
        'document',
        'source',
        'data',
        'notes',
        'last_contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'data' => 'array',
            'last_contacted_at' => 'datetime',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LeadStatusHistory::class)->latest();
    }

    public function assignmentHistories(): HasMany
    {
        return $this->hasMany(LeadAssignmentHistory::class)->latest();
    }

    public function scopeByStatus($query, LeadStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', LeadStatus::activePipeline());
    }

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('owner_id', $userId);
    }

    public function scopeByUnit($query, int $unitId)
    {
        return $query->where('unit_id', $unitId);
    }
}
