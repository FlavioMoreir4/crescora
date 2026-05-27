<?php

namespace Tests\Domains\Shared;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;

abstract class BaseFeatureTest extends BaseDomainTestCase
{
    protected Team $team;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->team = Team::factory()->create(['name' => 'Test Team', 'slug' => 'test-team']);
        $this->owner = User::factory()->create();
        $this->team->members()->attach($this->owner, ['role' => TeamRole::Owner->value]);
        $this->owner->current_team_id = $this->team->id;
        $this->owner->save();

        $this->actingAs($this->owner);
    }

    protected function switchTeam(Team $team): void
    {
        $this->owner->current_team_id = $team->id;
        $this->owner->save();
    }
}
