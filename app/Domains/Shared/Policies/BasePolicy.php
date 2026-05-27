<?php

declare(strict_types=1);

namespace App\Domains\Shared\Policies;

use App\Domains\Shared\Context\TenantContext;
use App\Domains\Shared\Exceptions\DomainException;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Exceptions\UnauthorizedException;

abstract class BasePolicy
{
    use HandlesAuthorization;

    protected function requireTeam(): void
    {
        if (TenantContext::getTeamId() === null) {
            throw new DomainException('Nenhum time selecionado para realizar esta ação.');
        }
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

    protected function deny(string $message = 'Esta ação não é autorizada.'): void
    {
        throw UnauthorizedException::forPermissions([$message]);
    }
}
