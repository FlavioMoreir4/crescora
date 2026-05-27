<?php

declare(strict_types=1);

use App\Domains\Shared\Context\TenantContext;
use App\Models\Form;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    app()['cache']->forget('spatie.permission.cache');
    TenantContext::setTeamId(null);
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('guests cannot access forms', function () {
    $response = $this->get(route('forms.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can view forms index', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    Form::factory()->count(3)->create([
        'team_id' => $team->id,
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->get(route('forms.index'));

    $response->assertOk();
});

test('authenticated user can create a form with fields', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');

    $response = $this
        ->actingAs($user)
        ->post(route('forms.store'), [
            'name' => 'Test Form',
            'description' => 'A test form description',
            'is_active' => true,
            'fields' => [
                [
                    'type' => 'text',
                    'name' => 'full_name',
                    'label' => 'Full Name',
                    'is_required' => true,
                    'order' => 1,
                ],
                [
                    'type' => 'email',
                    'name' => 'email_address',
                    'label' => 'Email Address',
                    'is_required' => true,
                    'order' => 2,
                ],
            ],
        ]);

    $response->assertRedirect();
    $form = Form::where('name', 'Test Form')->first();
    expect($form)->not->toBeNull();
    expect($form->fields()->count())->toBe(2);
});

test('authenticated user can view a form', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    $form = Form::factory()->create([
        'team_id' => $team->id,
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->get(route('forms.show', $form));

    $response->assertOk();
    $response->assertSee($form->name);
});

test('authenticated user can update a form', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    $form = Form::factory()->create([
        'team_id' => $team->id,
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->put(route('forms.update', $form), [
            'name' => 'Updated Form',
        ]);

    $response->assertRedirect();
    expect($form->fresh()->name)->toBe('Updated Form');
});

test('authenticated user can delete a form', function () {
    $user = User::factory()->create();
    TenantContext::setTeamId($user->currentTeam->id);
    $user->assignRole('admin');
    $team = $user->currentTeam;

    TenantContext::setIgnoreTenancy(true);
    $form = Form::factory()->create([
        'team_id' => $team->id,
    ]);
    TenantContext::setIgnoreTenancy(false);

    $response = $this
        ->actingAs($user)
        ->delete(route('forms.destroy', $form));

    $response->assertRedirect(route('forms.index'));
    expect($form->fresh())->toBeNull();
});
