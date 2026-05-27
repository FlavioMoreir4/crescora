<?php

declare(strict_types=1);

namespace App\Domains\Units\Policies;

use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UnitPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        $this->requireTeam();

        return true;
    }

    public function view(?User $user, Model $model): bool
    {
        return $this->belongsToTeam($model);
    }

    public function create(?User $user): bool
    {
        $this->requireTeam();

        return true;
    }

    public function update(?User $user, Model $model): bool
    {
        return $this->belongsToTeam($model);
    }

    public function delete(?User $user, Model $model): bool
    {
        return $this->belongsToTeam($model);
    }

    public function restore(?User $user, Model $model): bool
    {
        return $this->belongsToTeam($model);
    }

    public function forceDelete(?User $user, Model $model): bool
    {
        return false;
    }
}
