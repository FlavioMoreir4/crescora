<?php

declare(strict_types=1);

namespace App\Domains\Units\Policies;

use App\Domains\Shared\Policies\BasePolicy;
use App\Domains\Units\Models\Unit;
use App\Models\User;

class UnitPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool
    {
        $this->requireTeam();

        return true;
    }

    public function view(?User $user, Unit $unit): bool
    {
        return $this->belongsToTeam($unit);
    }

    public function create(?User $user): bool
    {
        $this->requireTeam();

        return true;
    }

    public function update(?User $user, Unit $unit): bool
    {
        return $this->belongsToTeam($unit);
    }

    public function delete(?User $user, Unit $unit): bool
    {
        return $this->belongsToTeam($unit);
    }

    public function restore(?User $user, Unit $unit): bool
    {
        return $this->belongsToTeam($unit);
    }

    public function forceDelete(?User $user, Unit $unit): bool
    {
        return false;
    }
}
