<?php

namespace Tests\Helpers;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;

class TeamTestHelper
{
    public static function createTeamWithOwner(array $teamData = []): array
    {
        $team = Team::factory()->create($teamData);
        $owner = User::factory()->create();
        $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
        $owner->current_team_id = $team->id;
        $owner->save();

        return ['team' => $team, 'owner' => $owner];
    }

    public static function addMember(Team $team, string $role = TeamRole::Member->value): User
    {
        $user = User::factory()->create();
        $team->members()->attach($user, ['role' => $role]);

        return $user;
    }

    public static function actingAsOwner(Team $team, User $owner): void
    {
        $owner->current_team_id = $team->id;
        $owner->save();
        auth()->login($owner);
    }
}
