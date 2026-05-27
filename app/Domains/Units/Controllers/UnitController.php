<?php

declare(strict_types=1);

namespace App\Domains\Units\Controllers;

use App\Domains\Shared\Context\TenantContext;
use App\Domains\Units\Models\Unit;
use App\Domains\Units\Requests\StoreUnitRequest;
use App\Domains\Units\Requests\UpdateUnitRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class UnitController
{
    public function index(): Response
    {
        \Gate::authorize('viewAny', Unit::class);

        $units = Unit::query()
            ->forCurrentTeam()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('units/Index', [
            'units' => $units,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create(): Response
    {
        \Gate::authorize('create', Unit::class);

        return Inertia::render('units/Create');
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        \Gate::authorize('create', Unit::class);

        $unit = Unit::query()->create([
            ...$request->validated(),
            'team_id' => TenantContext::getTeamId(),
        ]);

        return to_route('units.show', $unit);
    }

    public function show(Unit $unit): Response
    {
        \Gate::authorize('view', $unit);

        return Inertia::render('units/Show', [
            'unit' => $unit,
        ]);
    }

    public function edit(Unit $unit): Response
    {
        \Gate::authorize('update', $unit);

        return Inertia::render('units/Edit', [
            'unit' => $unit,
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        \Gate::authorize('update', $unit);

        $unit->update($request->validated());

        return to_route('units.show', $unit);
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        \Gate::authorize('delete', $unit);

        $unit->delete();

        return to_route('units.index');
    }
}
