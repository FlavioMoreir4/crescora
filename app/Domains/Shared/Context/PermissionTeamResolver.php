<?php

declare(strict_types=1);

namespace App\Domains\Shared\Context;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

final class PermissionTeamResolver implements PermissionsTeamResolver
{
    public function setPermissionsTeamId(int|string|Model|null $id): void
    {
        $teamId = $id instanceof Model ? $id->getKey() : $id;

        TenantContext::setTeamId($teamId !== null ? (int) $teamId : null);
    }

    public function getPermissionsTeamId(): int|string|null
    {
        return TenantContext::getTeamId();
    }
}
