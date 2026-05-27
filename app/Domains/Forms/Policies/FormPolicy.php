<?php

declare(strict_types=1);

namespace App\Domains\Forms\Policies;

use App\Domains\Forms\Models\Form;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class FormPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        $this->requireTeam();

        return $user?->hasPermissionTo('forms.view') ?? false;
    }

    public function view(?User $user, Form $form): bool
    {
        return $this->belongsToTeam($form)
            && ($user?->hasPermissionTo('forms.view') ?? false);
    }

    public function create(?User $user): bool
    {
        $this->requireTeam();

        return $user?->hasPermissionTo('forms.create') ?? false;
    }

    public function update(?User $user, Form $form): bool
    {
        return $this->belongsToTeam($form)
            && ($user?->hasPermissionTo('forms.edit') ?? false);
    }

    public function delete(?User $user, Form $form): bool
    {
        return $this->belongsToTeam($form)
            && ($user?->hasPermissionTo('forms.delete') ?? false);
    }

    public function restore(?User $user, Form $form): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(?User $user, Form $form): bool
    {
        return $this->isAdmin($user);
    }
}
