<?php

declare(strict_types=1);

namespace App\Domains\Distribution\Strategies;

use App\Domains\Distribution\Contracts\DistributionStrategy;
use App\Domains\Leads\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoundRobinStrategy implements DistributionStrategy
{
    public function assign(Lead $lead): ?User
    {
        $teamId = $lead->team_id;
        $unitId = $lead->unit_id;

        return DB::transaction(function () use ($teamId) {
            $users = User::query()
                ->where('current_team_id', $teamId)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['gestor', 'vendedor']))
                ->select('users.id')
                ->lockForUpdate()
                ->get();

            if ($users->isEmpty()) {
                return null;
            }

            $lastAssignment = Lead::query()
                ->where('team_id', $teamId)
                ->whereNotNull('owner_id')
                ->orderByDesc('created_at')
                ->lockForUpdate()
                ->first();

            if (! $lastAssignment?->owner_id) {
                return User::find($users->first()->id);
            }

            $lastIndex = $users->search(fn ($u) => $u->id === $lastAssignment->owner_id);

            if ($lastIndex === false) {
                return User::find($users->first()->id);
            }

            $nextIndex = ($lastIndex + 1) % $users->count();

            return User::find($users[$nextIndex]->id);
        });
    }
}
