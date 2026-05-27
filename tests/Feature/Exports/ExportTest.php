<?php

declare(strict_types=1);

use App\Domains\Shared\Context\TenantContext;
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
