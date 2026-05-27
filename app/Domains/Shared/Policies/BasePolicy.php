<?php

declare(strict_types=1);

namespace App\Domains\Shared\Policies;

use App\Domains\Shared\Context\TenantContext;
use App\Domains\Shared\Exceptions\DomainException;
use App\Models\Team;
use App\Models\User;
use App\Enums\TeamResourceAccessLevel;
use App\Enums\TeamResourceType;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Spatie\Permission\Exceptions\UnauthorizedException;

abstract class BasePolicy
{
    use HandlesAuthorization;

    protected string $resource = '';

    protected function requireTeam(): void
    {
        if (TenantContext::getTeamId() === null) {
            throw new DomainException('Nenhum time selecionado para realizar esta ação.');
        }
    }

    protected function currentTeam(): ?Team
    {
        return TenantContext::currentTeam();
    }

    protected function isSystemAdmin(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->isSystemAdmin() ?? false;
    }

    protected function isCurrentTeamOwner(?User $user = null): bool
    {
        $user ??= auth()->user();
        $team = $this->currentTeam();

        if ($user === null || $team === null) {
            return false;
        }

        return $user->ownsTeam($team);
    }

    protected function belongsToTeam(Model $model): bool
    {
        $teamId = TenantContext::getTeamId();

        if ($teamId === null || ! method_exists($model, 'getAttribute')) {
            return false;
        }

        $modelTeamId = $model->getAttribute('team_id');

        if ($modelTeamId === null) {
            return true;
        }

        return (int) $modelTeamId === $teamId;
    }

    protected function isOwner(Model $model, ?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user === null || ! method_exists($model, 'getAttribute')) {
            return false;
        }

        $ownerId = $model->getAttribute('user_id') ?? $model->getAttribute('owner_id');

        return $ownerId !== null && (int) $ownerId === $user->id;
    }

    protected function isAdmin(?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user === null) {
            return false;
        }

        return $user->hasRole(['owner', 'admin']);
    }

    protected function hasPermission(string $permission, ?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user === null) {
            return false;
        }

        return $user->hasPermissionTo($permission);
    }

    protected function hasAnyPermission(array|string $permissions, ?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user === null) {
            return false;
        }

        return $user->hasAnyPermission(Arr::wrap($permissions));
    }

    protected function hasResourceAccess(
        Model $model,
        TeamResourceAccessLevel $minimumLevel = TeamResourceAccessLevel::View,
        ?User $user = null,
    ): bool {
        $user ??= auth()->user();
        $team = $this->currentTeam();

        if ($user === null || $team === null || ! method_exists($model, 'getKey')) {
            return false;
        }

        $resourceType = $this->resolveResourceType($model);

        if ($resourceType === null) {
            return false;
        }

        return $user->hasResourceAccess($resourceType, (int) $model->getKey(), $minimumLevel, $team->id);
    }

    protected function hasAnyResourceAccess(
        TeamResourceType $resourceType,
        ?User $user = null,
        TeamResourceAccessLevel $minimumLevel = TeamResourceAccessLevel::View,
    ): bool {
        $user ??= auth()->user();
        $team = $this->currentTeam();

        if ($user === null || $team === null) {
            return false;
        }

        return $user->hasAnyResourceAccess($resourceType, $minimumLevel, $team->id);
    }

    protected function resolveResourceType(Model $model): ?TeamResourceType
    {
        if (! method_exists($model, 'resourceType')) {
            return null;
        }

        $resourceType = $model::resourceType();

        return $resourceType instanceof TeamResourceType ? $resourceType : null;
    }

    protected function canAccessCurrentTeam(array|string $permissions, ?User $user = null): bool
    {
        $this->requireTeam();

        return $this->isSystemAdmin($user)
            || $this->isCurrentTeamOwner($user)
            || $this->hasAnyPermission($permissions, $user);
    }

    protected function canAccessTeamModel(Model $model, array|string $permissions, ?User $user = null): bool
    {
        if (! $this->belongsToTeam($model)) {
            return false;
        }

        return $this->isSystemAdmin($user)
            || $this->isCurrentTeamOwner($user)
            || $this->hasAnyPermission($permissions, $user);
    }

    protected function canAccessTeamModelWithAllowlist(
        Model $model,
        array|string $permissions,
        TeamResourceAccessLevel $minimumLevel = TeamResourceAccessLevel::View,
        ?User $user = null,
    ): bool {
        if (! $this->belongsToTeam($model)) {
            return false;
        }

        return $this->isSystemAdmin($user)
            || $this->isCurrentTeamOwner($user)
            || $this->hasAnyPermission($permissions, $user)
            || $this->hasResourceAccess($model, $minimumLevel, $user);
    }

    protected function deny(string $message = 'Esta ação não é autorizada.'): void
    {
        throw UnauthorizedException::forPermissions([$message]);
    }

    public function viewAny(?User $user): bool
    {
        $this->requireTeam();

        return $this->hasPermission("{$this->resource}.viewAny");
    }

    public function view(?User $user, Model $model): bool
    {
        return $this->belongsToTeam($model)
            && $this->hasPermission("{$this->resource}.view");
    }

    public function create(?User $user): bool
    {
        $this->requireTeam();

        return $this->hasPermission("{$this->resource}.create");
    }

    public function update(?User $user, Model $model): bool
    {
        return $this->belongsToTeam($model)
            && $this->hasPermission("{$this->resource}.update");
    }

    public function delete(?User $user, Model $model): bool
    {
        return $this->belongsToTeam($model)
            && $this->hasPermission("{$this->resource}.delete");
    }
}
