<?php

declare(strict_types=1);

namespace App\Domains\Shared\Models\Scopes;

use App\Domains\Shared\Context\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (TenantContext::isIgnoreTenancy()) {
            return;
        }

        $teamId = TenantContext::getTeamId();

        if ($teamId !== null) {
            $builder->where('team_id', '=', $teamId);
        }
    }
}
