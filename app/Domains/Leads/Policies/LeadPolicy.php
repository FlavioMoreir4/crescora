<?php

declare(strict_types=1);

namespace App\Domains\Leads\Policies;

use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;
use App\Support\DomainPermissions;
use Illuminate\Database\Eloquent\Model;

class LeadPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->canAccessCurrentTeam([
            ...DomainPermissions::leadsView(),
            ...DomainPermissions::leadsViewOwn(),
        ], $user);
    }

    public function view(?User $user, Model $model): bool
    {
        if ($this->canAccessTeamModel($model, DomainPermissions::leadsView(), $user)) {
            return true;
        }

        return $this->belongsToTeam($model)
            && $this->hasAnyPermission(DomainPermissions::leadsViewOwn(), $user)
            && $this->isOwner($model, $user);
    }

    public function create(?User $user): bool
    {
        return $this->canAccessCurrentTeam(DomainPermissions::leadsCreate(), $user);
    }

    public function update(?User $user, Model $model): bool
    {
        if ($this->canAccessTeamModel($model, DomainPermissions::leadsUpdate(), $user)) {
            return true;
        }

        return $this->belongsToTeam($model)
            && $this->hasAnyPermission(DomainPermissions::leadsUpdateOwn(), $user)
            && $this->isOwner($model, $user);
    }

    public function transfer(?User $user, Model $model): bool
    {
        return $this->canAccessTeamModel($model, DomainPermissions::leadsTransfer(), $user);
    }

    public function delete(?User $user, Model $model): bool
    {
        return $this->canAccessTeamModel($model, DomainPermissions::leadsDelete(), $user);
    }

    public function restore(?User $user, Model $model): bool
    {
        return $this->canAccessTeamModel($model, DomainPermissions::leadsDelete(), $user);
    }

    public function forceDelete(?User $user, Model $model): bool
    {
        return $this->canAccessTeamModel($model, DomainPermissions::leadsDelete(), $user);
    }
}
