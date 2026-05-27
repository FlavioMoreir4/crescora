<?php

declare(strict_types=1);

namespace App\Domains\Forms\Controllers;

use App\Domains\Forms\Models\Form;
use App\Domains\Units\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PublicFormController
{
    public function show(string $teamSlug, string $unitSlug, string $formSlug): JsonResponse
    {
        $form = Form::query()
            ->where('slug', $formSlug)
            ->whereHas('team', function ($query) use ($teamSlug) {
                $query->where('slug', $teamSlug);
            })
            ->with('fields')
            ->firstOrFail();

        return response()->json([
            'form' => $form,
        ]);
    }

    public function submit(Request $request, string $teamSlug, string $unitSlug, string $formSlug): JsonResponse
    {
        $form = Form::query()
            ->where('slug', $formSlug)
            ->whereHas('team', function ($query) use ($teamSlug) {
                $query->where('slug', $teamSlug);
            })
            ->firstOrFail();

        $unit = Unit::query()
            ->where('slug', $unitSlug)
            ->whereHas('team', function ($query) use ($teamSlug) {
                $query->where('slug', $teamSlug);
            })
            ->firstOrFail();

        $validated = $request->validate(
            $form->fields->mapWithKeys(function ($field) {
                $rules = ['nullable'];

                if ($field->is_required) {
                    $rules = ['required'];
                }

                $rules[] = match ($field->type) {
                    'email' => 'email',
                    'tel' => 'phone',
                    'number' => 'numeric',
                    'cpf' => 'cpf',
                    'cnpj' => 'cnpj',
                    'cep' => 'formato_cep',
                    default => 'string',
                };

                return ["data.{$field->name}" => $rules];
            })->toArray()
        );

        $submission = $form->submissions()->create([
            'unit_id' => $unit->id,
            'data' => $validated['data'] ?? [],
        ]);

        return response()->json([
            'message' => 'Formulário enviado com sucesso.',
            'submission' => $submission,
        ], 201);
    }
}
