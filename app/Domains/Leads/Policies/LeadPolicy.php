<?php

declare(strict_types=1);

namespace App\Domains\Leads\Policies;

use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LeadPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        $this->requireTeam();

        return $user?->hasPermissionTo('leads.view') ?? false
            || $user?->hasPermissionTo('leads.view-own') ?? false;
    }

    public function view(?User $user, Model $model): bool
    {
        if (! $this->belongsToTeam($model)) {
            return false;
        }

        if ($user?->hasPermissionTo('leads.view') ?? false) {
            return true;
        }

        return ($user?->hasPermissionTo('leads.view-own') ?? false)
            && $this->isOwner($model, $user);
    }

    public function create(?User $user): bool
    {
        $this->requireTeam();

        return $user?->hasPermissionTo('leads.create') ?? false;
    }

    public function update(?User $user, Model $model): bool
    {
        if (! $this->belongsToTeam($model)) {
            return false;
        }

        if ($user?->hasPermissionTo('leads.edit') ?? false) {
            return true;
        }

        return ($user?->hasPermissionTo('leads.edit-own') ?? false)
            && $this->isOwner($model, $user);
    }

    public function delete(?User $user, Model $model): bool
    {
        if (! $this->belongsToTeam($model)) {
            return false;
        }

        return $user?->hasPermissionTo('leads.delete') ?? false;
    }

    public function restore(?User $user, Model $model): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(?User $user, Model $model): bool
    {
        return $this->isAdmin($user);
    }
}
