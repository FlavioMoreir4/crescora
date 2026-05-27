<?php

declare(strict_types=1);

namespace App\Domains\Forms\Controllers;

use App\Domains\Forms\Models\Form;
use App\Domains\Forms\Models\FormField;
use App\Domains\Forms\Requests\StoreFormRequest;
use App\Domains\Forms\Requests\UpdateFormRequest;
use App\Domains\Shared\Context\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class FormController
{
    public function index(): Response
    {
        \Gate::authorize('viewAny', Form::class);

        $forms = Form::query()
            ->forCurrentTeam()
            ->withCount('fields', 'submissions')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('forms/Index', [
            'forms' => $forms,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create(): Response
    {
        \Gate::authorize('create', Form::class);

        return Inertia::render('forms/Create', [
            'fieldTypes' => FormField::allowedTypes(),
        ]);
    }

    public function store(StoreFormRequest $request): RedirectResponse
    {
        \Gate::authorize('create', Form::class);

        $form = Form::query()->create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'config' => $request->config,
            'team_id' => TenantContext::getTeamId(),
        ]);

        if ($fields = $request->fields) {
            $form->fields()->createMany($fields);
        }

        return to_route('forms.show', $form);
    }

    public function show(Form $form): Response
    {
        \Gate::authorize('view', $form);

        $form->load('fields', 'submissions');

        return Inertia::render('forms/Show', [
            'form' => $form,
        ]);
    }

    public function edit(Form $form): Response
    {
        \Gate::authorize('update', $form);

        $form->load('fields');

        return Inertia::render('forms/Edit', [
            'form' => $form,
            'fieldTypes' => FormField::allowedTypes(),
        ]);
    }

    public function update(UpdateFormRequest $request, Form $form): RedirectResponse
    {
        \Gate::authorize('update', $form);

        $form->update($request->safe()->except('fields'));

        if ($request->has('fields')) {
            $form->fields()->delete();
            $form->fields()->createMany($request->fields);
        }

        return to_route('forms.show', $form);
    }

    public function destroy(Form $form): RedirectResponse
    {
        \Gate::authorize('delete', $form);

        $form->delete();

        return to_route('forms.index');
    }
}
