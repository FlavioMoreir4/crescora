<?php

declare(strict_types=1);

use App\Domains\Export\Models\Export;
use App\Domains\Shared\Context\TenantContext;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    app()['cache']->forget('spatie.permission.cache');
    TenantContext::setTeamId(null);
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('guests cannot access exports', function () {
    $response = $this->get(route('exports.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can view exports index', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');

    $response = $this
        ->actingAs($user)
        ->get(route('exports.index'));

    $response->assertOk();
});

test('export downloads are blocked for another team', function () {
    $user = User::factory()->create();
    $otherTeam = Team::factory()->create([
        'name' => 'Other Team',
        'slug' => 'other-team',
    ]);

    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');

    TenantContext::setIgnoreTenancy(true);
    $export = Export::query()->create([
        'team_id' => $otherTeam->id,
        'user_id' => $user->id,
        'type' => 'leads',
        'status' => 'completed',
        'file_path' => 'exports/other-team/leads.xlsx',
        'file_name' => 'leads.xlsx',
        'completed_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('exports.download', $export));

    $response->assertForbidden();

    TenantContext::setIgnoreTenancy(false);
});
