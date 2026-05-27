<?php

declare(strict_types=1);

use App\Domains\Forms\Models\FormField;
use App\Domains\Forms\Models\FormSubmission;
use App\Domains\Forms\Models\FormSubmissionValue;
use App\Domains\Leads\Models\LeadAssignmentHistory;
use App\Domains\Shared\Context\TenantContext;
use App\Models\Form;
use App\Models\Lead;
use App\Models\Team;
use App\Models\Unit;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    TenantContext::setTeamId(null);
    TenantContext::setIgnoreTenancy(false);
});

test('public form page renders for a team unit form', function () {
    $team = Team::factory()->create([
        'slug' => 'acme',
        'name' => 'Acme',
    ]);
    $unit = Unit::factory()->create([
        'team_id' => $team->id,
        'slug' => 'sao-paulo',
        'name' => 'São Paulo',
    ]);
    $form = Form::query()->create([
        'team_id' => $team->id,
        'slug' => 'lead-capture',
        'name' => 'Lead Capture',
        'description' => 'Captação pública de leads.',
        'is_active' => true,
    ]);
    FormField::query()->create([
        'form_id' => $form->id,
        'type' => 'text',
        'name' => 'name',
        'label' => 'Nome',
        'is_required' => true,
        'order' => 1,
    ]);

    $this->get(route('public.forms.show', [
        'teamSlug' => $team->slug,
        'unitSlug' => $unit->slug,
        'formSlug' => $form->slug,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('forms/Public')
            ->where('team.slug', 'acme')
            ->where('unit.slug', 'sao-paulo')
            ->where('form.slug', 'lead-capture'),
        );
});

test('public form submission creates submission and lead', function () {
    $team = Team::factory()->create([
        'slug' => 'acme',
        'name' => 'Acme',
    ]);
    $unit = Unit::factory()->create([
        'team_id' => $team->id,
        'slug' => 'sao-paulo',
        'name' => 'São Paulo',
    ]);
    $form = Form::query()->create([
        'team_id' => $team->id,
        'slug' => 'lead-capture',
        'name' => 'Lead Capture',
        'description' => 'Captação pública de leads.',
        'is_active' => true,
    ]);

    FormField::query()->create([
        'form_id' => $form->id,
        'type' => 'text',
        'name' => 'name',
        'label' => 'Nome',
        'is_required' => true,
        'order' => 1,
    ]);
    FormField::query()->create([
        'form_id' => $form->id,
        'type' => 'email',
        'name' => 'email',
        'label' => 'E-mail',
        'is_required' => true,
        'order' => 2,
    ]);
    FormField::query()->create([
        'form_id' => $form->id,
        'type' => 'tel',
        'name' => 'phone',
        'label' => 'Telefone',
        'is_required' => true,
        'order' => 3,
    ]);
    FormField::query()->create([
        'form_id' => $form->id,
        'type' => 'textarea',
        'name' => 'message',
        'label' => 'Mensagem',
        'is_required' => false,
        'order' => 4,
    ]);

    $response = $this
        ->from(route('public.forms.show', [
            'teamSlug' => $team->slug,
            'unitSlug' => $unit->slug,
            'formSlug' => $form->slug,
        ]))
        ->post(route('public.forms.submit', [
            'teamSlug' => $team->slug,
            'unitSlug' => $unit->slug,
            'formSlug' => $form->slug,
        ]), [
            'data' => [
                'name' => 'Maria Silva',
                'email' => 'maria@example.com',
                'phone' => '11999999999',
                'message' => 'Quero receber contato.',
            ],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    expect(FormSubmission::query()->count())->toBe(1);
    expect(FormSubmissionValue::query()->count())->toBe(4);
    expect(Lead::query()->count())->toBe(1);

    $lead = Lead::query()->firstOrFail();

    expect($lead->team_id)->toBe($team->id);
    expect($lead->unit_id)->toBe($unit->id);
    expect($lead->name)->toBe('Maria Silva');
    expect($lead->email)->toBe('maria@example.com');
    expect($lead->phone)->toBe('11999999999');
    expect($lead->source)->toBe('form:lead-capture');
});

test('public form submission honors a fixed lead owner', function () {
    $team = Team::factory()->create([
        'slug' => 'acme',
        'name' => 'Acme',
    ]);
    $unit = Unit::factory()->create([
        'team_id' => $team->id,
        'slug' => 'sao-paulo',
        'name' => 'São Paulo',
    ]);
    $owner = User::factory()->create();
    $team->members()->attach($owner, ['role' => \App\Enums\TeamRole::Owner->value]);

    $form = Form::query()->create([
        'team_id' => $team->id,
        'slug' => 'lead-capture',
        'name' => 'Lead Capture',
        'description' => 'Captação pública de leads.',
        'is_active' => true,
        'config' => [
            'lead_assignment' => [
                'mode' => 'fixed',
                'owner_id' => $owner->id,
            ],
        ],
    ]);

    FormField::query()->create([
        'form_id' => $form->id,
        'type' => 'text',
        'name' => 'name',
        'label' => 'Nome',
        'is_required' => true,
        'order' => 1,
    ]);
    TenantContext::setTeamId($team->id);

    $this->post(route('public.forms.submit', [
        'teamSlug' => $team->slug,
        'unitSlug' => $unit->slug,
        'formSlug' => $form->slug,
    ]), [
        'data' => [
            'name' => 'Maria Silva',
        ],
    ])->assertOk();

    $this->assertDatabaseHas('leads', [
        'team_id' => $team->id,
        'unit_id' => $unit->id,
        'owner_id' => $owner->id,
        'name' => 'Maria Silva',
        'source' => 'form:lead-capture',
    ]);
    expect(LeadAssignmentHistory::withoutGlobalScopes()->count())->toBe(1);
    expect(LeadAssignmentHistory::withoutGlobalScopes()->firstOrFail()->source)->toBe('form');
});
