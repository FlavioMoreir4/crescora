<?php

declare(strict_types=1);

namespace App\Domains\Export\Policies;

use App\Domains\Export\Models\Export;
use App\Domains\Shared\Context\TenantContext;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class ExportPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        $this->requireTeam();

        return $user?->hasPermissionTo('reports.export') ?? false;
    }

    public function view(?User $user, Export $export): bool
    {
        if (TenantContext::getTeamId() === null) {
            return false;
        }

        if ((int) $export->team_id !== TenantContext::getTeamId()) {
            return false;
        }

        return $user?->hasPermissionTo('reports.export') ?? false;
    }

    public function create(?User $user): bool
    {
        $this->requireTeam();

        return $user?->hasPermissionTo('reports.export') ?? false;
    }
}
