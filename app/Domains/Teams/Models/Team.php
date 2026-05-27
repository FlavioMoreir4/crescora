<?php

declare(strict_types=1);

namespace App\Domains\Teams\Models;

use App\Concerns\GeneratesUniqueTeamSlugs;
use App\Domains\Billing\Models\Subscription;
use App\Domains\Teams\Models\TeamResourceAccess;
use App\Domains\Users\Models\User;
use App\Enums\TeamRole;
use App\Models\Membership;
use App\Models\TeamInvitation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use GeneratesUniqueTeamSlugs, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'is_personal',
        'gateway_customer_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Team $team) {
            if (empty($team->slug)) {
                $team->slug = static::generateUniqueTeamSlug($team->name);
            }
        });

        static::updating(function (Team $team) {
            if ($team->isDirty('name')) {
                $team->slug = static::generateUniqueTeamSlug($team->name, $team->id);
            }
        });
    }

    public function owner(): ?Model
    {
        return $this->members()
            ->wherePivot('role', TeamRole::Owner->value)
            ->first();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members', 'team_id', 'user_id')
            ->using(Membership::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * @return HasMany<TeamResourceAccess, $this>
     */
    public function resourceAccesses(): HasMany
    {
        return $this->hasMany(TeamResourceAccess::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    protected function casts(): array
    {
        return [
            'is_personal' => 'boolean',
        ];
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
