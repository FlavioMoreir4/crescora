<?php

declare(strict_types=1);

namespace App\Domains\Units\Policies;

use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;
use App\Support\DomainPermissions;
use App\Enums\TeamResourceAccessLevel;
use App\Enums\TeamResourceType;
use Illuminate\Database\Eloquent\Model;

class UnitPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->canAccessCurrentTeam(DomainPermissions::unitsView(), $user)
            || $this->hasAnyResourceAccess(TeamResourceType::Unit, $user);
    }

    public function view(?User $user, Model $model): bool
    {
        return $this->canAccessTeamModelWithAllowlist($model, DomainPermissions::unitsView(), TeamResourceAccessLevel::View, $user);
    }

    public function create(?User $user): bool
    {
        return $this->canAccessCurrentTeam(DomainPermissions::unitsCreate(), $user);
    }

    public function update(?User $user, Model $model): bool
    {
        return $this->canAccessTeamModelWithAllowlist($model, DomainPermissions::unitsUpdate(), TeamResourceAccessLevel::Manage, $user);
    }

    public function delete(?User $user, Model $model): bool
    {
        return $this->canAccessTeamModelWithAllowlist($model, DomainPermissions::unitsDelete(), TeamResourceAccessLevel::Manage, $user);
    }

    public function restore(?User $user, Model $model): bool
    {
        return $this->canAccessTeamModelWithAllowlist($model, DomainPermissions::unitsUpdate(), TeamResourceAccessLevel::Manage, $user);
    }

    public function forceDelete(?User $user, Model $model): bool
    {
        return $this->canAccessTeamModelWithAllowlist($model, DomainPermissions::unitsDelete(), TeamResourceAccessLevel::Manage, $user);
    }
}
