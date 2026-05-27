<?php

declare(strict_types=1);

namespace App\Domains\Forms\Policies;

use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FormPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        $this->requireTeam();

        return $user?->hasPermissionTo('forms.view') ?? false;
    }

    public function view(?User $user, Model $model): bool
    {
        return $this->belongsToTeam($model)
            && ($user?->hasPermissionTo('forms.view') ?? false);
    }

    public function create(?User $user): bool
    {
        $this->requireTeam();

        return $user?->hasPermissionTo('forms.create') ?? false;
    }

    public function update(?User $user, Model $model): bool
    {
        return $this->belongsToTeam($model)
            && ($user?->hasPermissionTo('forms.edit') ?? false);
    }

    public function delete(?User $user, Model $model): bool
    {
        return $this->belongsToTeam($model)
            && ($user?->hasPermissionTo('forms.delete') ?? false);
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
