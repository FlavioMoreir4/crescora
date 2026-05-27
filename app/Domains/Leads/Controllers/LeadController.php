<?php

declare(strict_types=1);

namespace App\Domains\Leads\Controllers;

use App\Domains\Leads\Enums\LeadStatus;
use App\Domains\Leads\Events\LeadCreated;
use App\Domains\Leads\Models\Lead;
use App\Domains\Leads\Requests\StoreLeadRequest;
use App\Domains\Leads\Requests\UpdateLeadRequest;
use App\Domains\Shared\Context\TenantContext;
use App\Domains\Units\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class LeadController
{
    public function index(): Response
    {
        \Gate::authorize('viewAny', Lead::class);

        $leads = Lead::query()
            ->forCurrentTeam()
            ->with(['unit', 'owner'])
            ->when(request('search'), fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when(request('status'), fn ($q, $v) => $q->byStatus(LeadStatus::tryFrom($v)))
            ->when(request('unit_id'), fn ($q, $v) => $q->byUnit((int) $v))
            ->when(request('owner_id'), fn ($q, $v) => $q->ownedBy((int) $v))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('leads/Index', [
            'leads' => $leads,
            'filters' => request()->only(['search', 'status', 'unit_id', 'owner_id']),
            'statuses' => collect(LeadStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
            'units' => Unit::forCurrentTeam()->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        \Gate::authorize('create', Lead::class);

        return Inertia::render('leads/Create', [
            'statuses' => collect(LeadStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
            'units' => Unit::forCurrentTeam()->get(['id', 'name']),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        \Gate::authorize('create', Lead::class);

        $lead = Lead::query()->create([
            ...$request->validated(),
            'team_id' => TenantContext::getTeamId(),
        ]);

        LeadCreated::dispatch($lead);

        return to_route('leads.show', $lead);
    }

    public function show(Lead $lead): Response
    {
        \Gate::authorize('view', $lead);

        $lead->load(['unit', 'owner', 'statusHistories.actor']);

        return Inertia::render('leads/Show', [
            'lead' => $lead,
            'statuses' => collect(LeadStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
        ]);
    }

    public function edit(Lead $lead): Response
    {
        \Gate::authorize('update', $lead);

        $lead->load(['unit', 'owner']);

        return Inertia::render('leads/Edit', [
            'lead' => $lead,
            'statuses' => collect(LeadStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
            'units' => Unit::forCurrentTeam()->get(['id', 'name']),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        \Gate::authorize('update', $lead);

        $lead->update($request->validated());

        return to_route('leads.show', $lead);
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        \Gate::authorize('delete', $lead);

        $lead->delete();

        return to_route('leads.index');
    }
}
