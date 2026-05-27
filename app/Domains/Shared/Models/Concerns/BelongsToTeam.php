<?php

declare(strict_types=1);

namespace App\Domains\Shared\Models\Concerns;

use App\Domains\Shared\Context\TenantContext;
use App\Domains\Shared\Models\Scopes\TenantScope;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTeam
{
    public static function bootBelongsToTeam(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function scopeForTeam(Builder $query, ?int $teamId = null): void
    {
        $query->where('team_id', '=', $teamId ?? TenantContext::getTeamId());
    }

    public function scopeForCurrentTeam(Builder $query): void
    {
        $query->where('team_id', '=', TenantContext::getTeamId());
    }
}
