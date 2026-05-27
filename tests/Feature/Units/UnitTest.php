<?php

declare(strict_types=1);

use App\Domains\Shared\Context\TenantContext;
use App\Models\Unit;
use App\Models\User;

test('guests cannot access units', function () {
    $response = $this->get(route('units.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can view units index', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    Unit::factory()->count(3)->create([
        'team_id' => $team->id,
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->get(route('units.index'));

    $response->assertOk();
});

test('authenticated user can create a unit', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('units.store'), [
            'name' => 'Test Unit',
            'description' => 'A test unit description',
            'is_active' => true,
        ]);

    $response->assertRedirect();
    expect(Unit::where('name', 'Test Unit')->exists())->toBeTrue();
});

test('authenticated user can update a unit', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    $unit = Unit::factory()->create([
        'team_id' => $team->id,
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->put(route('units.update', $unit), [
            'name' => 'Updated Unit',
        ]);

    $response->assertRedirect();
    expect($unit->fresh()->name)->toBe('Updated Unit');
});

test('authenticated user can delete a unit', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    $unit = Unit::factory()->create([
        'team_id' => $team->id,
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->delete(route('units.destroy', $unit));

    $response->assertRedirect(route('units.index'));
    $this->assertSoftDeleted($unit);
});

test('units index is scoped to current team', function () {
    $user1 = User::factory()->create();
    $team1 = $user1->currentTeam;
    $user2 = User::factory()->create();
    $team2 = $user2->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    Unit::factory()->create([
        'team_id' => $team1->id,
        'name' => 'Team 1 Unit',
        'slug' => 'team-1-unit',
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user2)
        ->get(route('units.index'));

    $response->assertOk();
    $response->assertDontSee('Team 1 Unit');
});
