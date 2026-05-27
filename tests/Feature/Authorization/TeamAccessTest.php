<?php

declare(strict_types=1);

use App\Domains\Billing\Models\Subscription;
use App\Domains\Forms\Models\Form;
use App\Domains\Leads\Models\Lead;
use App\Domains\Shared\Context\TenantContext;
use App\Domains\Teams\Models\TeamResourceAccess;
use App\Enums\TeamRole;
use App\Enums\TeamResourceAccessLevel;
use App\Enums\TeamResourceType;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Gate;
use Tests\Helpers\TeamTestHelper;

beforeEach(function () {
    app()['cache']->forget('spatie.permission.cache');
    TenantContext::setTeamId(null);
    TenantContext::setIgnoreTenancy(false);
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('team owner can access team resources without spatie permissions', function () {
    ['team' => $team, 'owner' => $owner] = TeamTestHelper::createTeamWithOwner([
        'name' => 'Owner Team',
        'slug' => 'owner-team',
    ]);

    expect(Gate::forUser($owner)->allows('viewAny', Unit::class))->toBeTrue();
    expect(Gate::forUser($owner)->allows('create', Form::class))->toBeTrue();
    expect(Gate::forUser($owner)->allows('viewAny', Subscription::class))->toBeTrue();
});

test('team admin with spatie permissions can access team resources', function () {
    ['team' => $team, 'owner' => $owner] = TeamTestHelper::createTeamWithOwner([
        'name' => 'Admin Team',
        'slug' => 'admin-team',
    ]);

    $admin = TeamTestHelper::addMember($team, TeamRole::Admin->value);
    $admin->switchTeam($team);
    $admin->assignRole('admin');

    expect(Gate::forUser($admin)->allows('viewAny', Unit::class))->toBeTrue();
    expect(Gate::forUser($admin)->allows('create', Form::class))->toBeTrue();
    expect(Gate::forUser($admin)->allows('viewAny', Subscription::class))->toBeTrue();
});

test('team member without permissions is forbidden from protected team routes', function () {
    ['team' => $team, 'owner' => $owner] = TeamTestHelper::createTeamWithOwner([
        'name' => 'Member Team',
        'slug' => 'member-team',
    ]);

    $member = TeamTestHelper::addMember($team, TeamRole::Member->value);
    $member->switchTeam($team);

    $this->actingAs($member)->get(route('units.index'))->assertForbidden();
    $this->actingAs($member)->get(route('forms.index'))->assertForbidden();
    $this->actingAs($member)->get(route('billing.index'))->assertForbidden();
});

test('team members with explicit resource access can view only allowed units and forms', function () {
    ['team' => $team, 'owner' => $owner] = TeamTestHelper::createTeamWithOwner([
        'name' => 'Scoped Team',
        'slug' => 'scoped-team',
    ]);

    $member = TeamTestHelper::addMember($team, TeamRole::Member->value);
    $member->switchTeam($team);

    TenantContext::setIgnoreTenancy(true);
    $allowedUnit = Unit::factory()->create([
        'team_id' => $team->id,
        'name' => 'Allowed Unit',
    ]);
    $blockedUnit = Unit::factory()->create([
        'team_id' => $team->id,
        'name' => 'Blocked Unit',
    ]);
    $allowedForm = Form::query()->create([
        'team_id' => $team->id,
        'name' => 'Allowed Form',
        'is_active' => true,
    ]);
    $blockedForm = Form::query()->create([
        'team_id' => $team->id,
        'name' => 'Blocked Form',
        'is_active' => true,
    ]);

    TeamResourceAccess::query()->create([
        'team_id' => $team->id,
        'user_id' => $member->id,
        'resource_type' => TeamResourceType::Unit,
        'resource_id' => $allowedUnit->id,
        'access_level' => TeamResourceAccessLevel::View,
        'granted_by' => $owner->id,
    ]);

    TeamResourceAccess::query()->create([
        'team_id' => $team->id,
        'user_id' => $member->id,
        'resource_type' => TeamResourceType::Form,
        'resource_id' => $allowedForm->id,
        'access_level' => TeamResourceAccessLevel::Manage,
        'granted_by' => $owner->id,
    ]);
    TenantContext::setIgnoreTenancy(false);

    $this->actingAs($member)
        ->get(route('units.index'))
        ->assertOk()
        ->assertSee('Allowed Unit')
        ->assertDontSee('Blocked Unit');

    $this->actingAs($member)
        ->get(route('forms.index'))
        ->assertOk()
        ->assertSee('Allowed Form')
        ->assertDontSee('Blocked Form');
});

test('cross team access is denied', function () {
    ['team' => $teamA, 'owner' => $ownerA] = TeamTestHelper::createTeamWithOwner([
        'name' => 'Team A',
        'slug' => 'team-a',
    ]);

    ['team' => $teamB, 'owner' => $ownerB] = TeamTestHelper::createTeamWithOwner([
        'name' => 'Team B',
        'slug' => 'team-b',
    ]);

    $adminB = TeamTestHelper::addMember($teamB, TeamRole::Admin->value);
    $adminB->switchTeam($teamB);
    $adminB->assignRole('admin');

    TenantContext::setIgnoreTenancy(true);
    $unitA = Unit::factory()->create(['team_id' => $teamA->id]);
    $leadA = Lead::create([
        'team_id' => $teamA->id,
        'unit_id' => $unitA->id,
        'owner_id' => $ownerA->id,
        'name' => 'Other Team Lead',
        'email' => 'other@example.com',
        'status' => 'new',
        'source' => 'website',
    ]);
    TenantContext::setIgnoreTenancy(false);

    expect(Gate::forUser($adminB)->allows('view', $leadA))->toBeFalse();
});

test('system admin bypasses tenant authorization', function () {
    $systemAdmin = User::factory()->systemAdmin()->create();

    TenantContext::setTeamId(null);

    expect(Gate::forUser($systemAdmin)->allows('viewAny', Unit::class))->toBeTrue();
    expect(Gate::forUser($systemAdmin)->allows('create', Form::class))->toBeTrue();
    expect(Gate::forUser($systemAdmin)->allows('viewAny', Subscription::class))->toBeTrue();
});
