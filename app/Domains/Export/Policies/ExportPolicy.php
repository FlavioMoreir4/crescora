<?php

declare(strict_types=1);

namespace App\Domains\Export\Policies;

use App\Domains\Shared\Context\TenantContext;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ExportPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        $this->requireTeam();

        return $user?->hasPermissionTo('reports.export') ?? false;
    }

    public function view(?User $user, Model $model): bool
    {
        if (TenantContext::getTeamId() === null) {
            return false;
        }

        if ((int) $model->team_id !== TenantContext::getTeamId()) {
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
