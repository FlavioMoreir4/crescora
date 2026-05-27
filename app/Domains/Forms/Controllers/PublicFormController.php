<?php

declare(strict_types=1);

namespace App\Domains\Forms\Controllers;

use App\Domains\Forms\Models\Form;
use App\Domains\Forms\Models\FormSubmission;
use App\Domains\Leads\Events\LeadCreated;
use App\Domains\Leads\Models\Lead;
use App\Domains\Leads\Services\LeadOwnershipService;
use App\Domains\Shared\Rules\DocumentRule;
use App\Domains\Shared\Rules\PhoneRule;
use App\Models\Team;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class PublicFormController
{
    public function show(Request $request, string $teamSlug, string $unitSlug, string $formSlug): JsonResponse|Response
    {
        $context = $this->resolveContext($teamSlug, $unitSlug, $formSlug);

        if ($request->expectsJson()) {
            return response()->json($context);
        }

        return Inertia::render('forms/Public', [
            ...$context,
            'submitUrl' => route('public.forms.submit', [
                'teamSlug' => $teamSlug,
                'unitSlug' => $unitSlug,
                'formSlug' => $formSlug,
            ]),
        ]);
    }

    public function submit(
        Request $request,
        string $teamSlug,
        string $unitSlug,
        string $formSlug,
        LeadOwnershipService $ownership,
    ): JsonResponse|RedirectResponse
    {
        $context = $this->resolveContext($teamSlug, $unitSlug, $formSlug);
        /** @var Form $form */
        $form = $context['form'];
        /** @var array{id:int, name:string, slug:string} $unit */
        $unit = $context['unit'];

        $validated = $request->validate([
            'data' => ['required', 'array'],
            ...$this->validationRules($form),
        ]);

        $data = $validated['data'];

        $result = DB::transaction(function () use ($form, $unit, $data, $ownership) {
            $submission = $form->submissions()->create([
                'unit_id' => $unit['id'],
                'data' => $data,
                'metadata' => [
                    'source' => 'public-form',
                    'form_slug' => $form->slug,
                    'unit_slug' => $unit['slug'],
                ],
            ]);

            $this->persistSubmissionValues($submission, $form, $data);

            $lead = Lead::query()->create([
                'team_id' => $form->team_id,
                'unit_id' => $unit['id'],
                'owner_id' => null,
                'name' => $this->firstValue($data, ['name', 'full_name', 'nome', 'nome_completo']) ?? $form->name,
                'email' => $this->firstValue($data, ['email', 'email_address', 'e-mail']),
                'phone' => $this->firstValue($data, ['phone', 'phone_number', 'telefone', 'whatsapp']),
                'document' => $this->firstValue($data, ['document', 'cpf', 'cnpj']),
                'source' => 'form:'.$form->slug,
                'data' => $data,
                'notes' => $this->firstValue($data, ['notes', 'message', 'observations', 'observacao']),
            ]);

            $assignmentOwnerId = $form->leadAssignmentOwnerId();
            if ($form->leadAssignmentMode() === 'fixed' && $assignmentOwnerId !== null) {
                $ownership->assign(
                    $lead,
                    \App\Models\User::query()->find($assignmentOwnerId),
                    null,
                    'form',
                    null,
                    false,
                );
            }

            return [
                'submission' => $submission->fresh(['values']),
                'lead' => $lead->fresh(['owner']),
            ];
        });

        LeadCreated::dispatch($result['lead']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Formulário enviado com sucesso.',
                ...$result,
            ], 201);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Formulário enviado com sucesso.',
        ]);

        return back();
    }

    private function resolveContext(string $teamSlug, string $unitSlug, string $formSlug): array
    {
        $team = Team::query()
            ->where('slug', $teamSlug)
            ->firstOrFail();

        $unit = Unit::query()
            ->where('slug', $unitSlug)
            ->where('team_id', $team->id)
            ->firstOrFail();

        $form = Form::query()
            ->where('slug', $formSlug)
            ->where('team_id', $team->id)
            ->with(['fields' => fn ($query) => $query->orderBy('order')])
            ->firstOrFail();

        return [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
            ],
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name,
                'slug' => $unit->slug,
            ],
            'form' => $form,
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function validationRules(Form $form): array
    {
        $rules = [];

        foreach ($form->fields as $field) {
            $rules["data.{$field->name}"] = $this->fieldRules($field->type, $field->is_required, $field->options ?? []);
        }

        return $rules;
    }

    /**
     * @param  array<int, string>  $options
     * @return array<int, mixed>
     */
    private function fieldRules(string $type, bool $required, array $options = []): array
    {
        $rules = $required ? ['required'] : ['nullable'];

        match ($type) {
            'email' => $rules[] = 'email',
            'number' => $rules[] = 'numeric',
            'date' => $rules[] = 'date',
            'cpf', 'cnpj' => $rules[] = new DocumentRule,
            'tel', 'phone', 'whatsapp' => $rules[] = new PhoneRule,
            'select', 'radio' => $rules[] = ! empty($options) ? Rule::in($options) : 'string',
            'checkbox' => $rules[] = 'boolean',
            default => $rules[] = 'string',
        };

        return $rules;
    }

    private function persistSubmissionValues(FormSubmission $submission, Form $form, array $data): void
    {
        foreach ($form->fields as $field) {
            $value = Arr::get($data, $field->name);

            $submission->values()->create([
                'field_id' => $field->id,
                'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $this->normalizeScalar($value),
                'value_type' => $field->type,
            ]);
        }
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function firstValue(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($data, $key);

            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === '' || $value === []) {
                continue;
            }

            return is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return null;
    }

    private function normalizeScalar(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
