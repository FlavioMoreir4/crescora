<?php

declare(strict_types=1);

namespace App\Domains\Billing\Policies;

use App\Domains\Billing\Models\Subscription;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;
use App\Support\DomainPermissions;
use Illuminate\Database\Eloquent\Model;

final class SubscriptionPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->canAccessCurrentTeam(DomainPermissions::billingManage(), $user);
    }

    public function view(?User $user, Model $model): bool
    {
        if (! $model instanceof Subscription) {
            return false;
        }

        return $this->canAccessTeamModel($model, DomainPermissions::billingManage(), $user);
    }

    public function create(?User $user): bool
    {
        return $this->canAccessCurrentTeam(DomainPermissions::billingManage(), $user);
    }

    public function update(?User $user, Model $model): bool
    {
        if (! $model instanceof Subscription) {
            return false;
        }

        return $this->canAccessTeamModel($model, DomainPermissions::billingManage(), $user);
    }

    public function delete(?User $user, Model $model): bool
    {
        return $this->update($user, $model);
    }
}
