<?php

declare(strict_types=1);

namespace App\Domains\Export\Policies;

use App\Domains\Shared\Context\TenantContext;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;
use App\Support\DomainPermissions;
use Illuminate\Database\Eloquent\Model;

class ExportPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->canAccessCurrentTeam(DomainPermissions::reportsExport(), $user);
    }

    public function view(?User $user, Model $model): bool
    {
        if (TenantContext::getTeamId() === null) {
            return false;
        }

        return $this->canAccessTeamModel($model, DomainPermissions::reportsExport(), $user);
    }

    public function create(?User $user): bool
    {
        return $this->canAccessCurrentTeam(DomainPermissions::reportsExport(), $user);
    }
}
