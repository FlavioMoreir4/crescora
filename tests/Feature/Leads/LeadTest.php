<?php

declare(strict_types=1);

use App\Domains\Shared\Context\TenantContext;
use App\Domains\Leads\Models\LeadAssignmentHistory;
use App\Enums\TeamRole;
use App\Models\Lead;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    app()['cache']->forget('spatie.permission.cache');
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('guests cannot access leads', function () {
    $response = $this->get(route('leads.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can view leads index', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    $unit = Unit::factory()->create(['team_id' => $team->id]);
    for ($i = 0; $i < 3; $i++) {
        Lead::create([
            'team_id' => $team->id,
            'unit_id' => $unit->id,
            'owner_id' => $user->id,
            'name' => "Lead {$i}",
            'email' => "lead{$i}@example.com",
            'status' => 'new',
            'source' => 'website',
        ]);
    }
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->get(route('leads.index'));

    $response->assertOk();
});

test('authenticated user can create a lead', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');

    $response = $this
        ->actingAs($user)
        ->post(route('leads.store'), [
            'name' => 'Test Lead',
            'email' => 'lead@example.com',
            'phone' => '11999999999',
            'source' => 'website',
        ]);

    $response->assertRedirect();
    expect(Lead::where('name', 'Test Lead')->exists())->toBeTrue();
});

test('authenticated user can view a lead', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    $unit = Unit::factory()->create(['team_id' => $team->id]);
    $lead = Lead::create([
        'team_id' => $team->id,
        'unit_id' => $unit->id,
        'owner_id' => $user->id,
        'name' => 'Test Lead View',
        'email' => 'view@example.com',
        'status' => 'new',
        'source' => 'website',
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->get(route('leads.show', $lead));

    $response->assertOk();
    $response->assertSee('Test Lead View');
});

test('authenticated user can update a lead', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    $unit = Unit::factory()->create(['team_id' => $team->id]);
    $lead = Lead::create([
        'team_id' => $team->id,
        'unit_id' => $unit->id,
        'owner_id' => $user->id,
        'name' => 'Original Lead',
        'email' => 'original@example.com',
        'status' => 'new',
        'source' => 'website',
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->put(route('leads.update', $lead), [
            'name' => 'Updated Lead',
        ]);

    $response->assertRedirect();
    expect($lead->fresh()->name)->toBe('Updated Lead');
});

test('authenticated user can delete a lead', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    $unit = Unit::factory()->create(['team_id' => $team->id]);
    $lead = Lead::create([
        'team_id' => $team->id,
        'unit_id' => $unit->id,
        'owner_id' => $user->id,
        'name' => 'Deletable Lead',
        'email' => 'delete@example.com',
        'status' => 'new',
        'source' => 'website',
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->delete(route('leads.destroy', $lead));

    $response->assertRedirect(route('leads.index'));
    $this->assertSoftDeleted($lead);
});

test('lead owner transfers are tracked in assignment history', function () {
    $owner = User::factory()->create();
    $team = $owner->currentTeam;
    $assignee = User::factory()->create();

    $team->members()->attach($assignee, ['role' => TeamRole::Member->value]);

    TenantContext::setTeamId($team->id);
    $owner->assignRole('admin');

    TenantContext::setIgnoreTenancy(true);
    $unit = Unit::factory()->create(['team_id' => $team->id]);
    $lead = Lead::create([
        'team_id' => $team->id,
        'unit_id' => $unit->id,
        'owner_id' => $owner->id,
        'name' => 'Transfer Lead',
        'email' => 'transfer@example.com',
        'status' => 'new',
        'source' => 'website',
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($owner)
        ->put(route('leads.update', $lead), [
            'name' => 'Transfer Lead',
            'owner_id' => $assignee->id,
        ]);

    $response->assertRedirect();

    expect($lead->fresh()->owner_id)->toBe($assignee->id);
    expect(LeadAssignmentHistory::query()->count())->toBe(2);
    expect(LeadAssignmentHistory::query()->latest('id')->first()?->source)->toBe('manual');
});

test('leads index is scoped to current team', function () {
    $user1 = User::factory()->create();
    $team1 = $user1->currentTeam;
    $user2 = User::factory()->create();
    TenantContext::setTeamId($user2->currentTeam->id);
    $user2->assignRole('admin');
    $team2 = $user2->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    $unit1 = Unit::factory()->create(['team_id' => $team1->id]);
    Lead::create([
        'team_id' => $team1->id,
        'unit_id' => $unit1->id,
        'owner_id' => $user1->id,
        'name' => 'Team 1 Lead',
        'email' => 'team1@example.com',
        'status' => 'new',
        'source' => 'website',
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user2)
        ->get(route('leads.index'));

    $response->assertOk();
    $response->assertDontSee('Team 1 Lead');
});

test('users with view-own only see their own leads on the index', function () {
    $owner = User::factory()->create();
    $team = $owner->currentTeam;

    $vendor = User::factory()->create();
    $team->members()->attach($vendor, ['role' => TeamRole::Member->value]);
    $vendor->switchTeam($team);

    TenantContext::setTeamId($team->id);
    $vendor->assignRole('vendedor');

    TenantContext::setIgnoreTenancy(true);
    $unit = Unit::factory()->create(['team_id' => $team->id]);
    Lead::create([
        'team_id' => $team->id,
        'unit_id' => $unit->id,
        'owner_id' => $vendor->id,
        'name' => 'Own Lead',
        'email' => 'own@example.com',
        'status' => 'new',
        'source' => 'website',
    ]);

    Lead::create([
        'team_id' => $team->id,
        'unit_id' => $unit->id,
        'owner_id' => $owner->id,
        'name' => 'Other Lead',
        'email' => 'other@example.com',
        'status' => 'new',
        'source' => 'website',
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($vendor)
        ->get(route('leads.index'));

    $response->assertOk();
    $response->assertSee('Own Lead');
    $response->assertDontSee('Other Lead');
});
