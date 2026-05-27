<?php

declare(strict_types=1);

namespace App\Domains\Leads\Controllers;

use App\Domains\Leads\Enums\LeadStatus;
use App\Domains\Leads\Events\LeadCreated;
use App\Domains\Leads\Models\Lead;
use App\Domains\Leads\Services\LeadOwnershipService;
use App\Domains\Leads\Requests\StoreLeadRequest;
use App\Domains\Leads\Requests\UpdateLeadRequest;
use App\Domains\Shared\Context\TenantContext;
use App\Domains\Units\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class LeadController
{
    public function index(): Response
    {
        \Gate::authorize('viewAny', Lead::class);

        $user = request()->user();
        $team = $user->currentTeam;

        $leads = Lead::query()
            ->forCurrentTeam()
            ->with(['unit', 'owner'])
            ->when(
                $team !== null
                && ! $user->isSystemAdmin()
                && ! $user->ownsTeam($team)
                && ! $user->hasPermissionTo('leads.view'),
                fn ($query) => $query->where('owner_id', $user->id),
            )
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
            'units' => Unit::query()
                ->forCurrentTeam()
                ->visibleTo($user)
                ->get(['id', 'name']),
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
            'units' => Unit::query()->forCurrentTeam()->visibleTo(request()->user())->get(['id', 'name']),
            'members' => $this->teamMembers(),
        ]);
    }

    public function store(StoreLeadRequest $request, LeadOwnershipService $ownership): RedirectResponse
    {
        \Gate::authorize('create', Lead::class);

        $validated = $request->validated();
        $hasOwnerId = array_key_exists('owner_id', $validated);
        $ownerId = $validated['owner_id'] ?? null;
        unset($validated['owner_id']);

        $lead = Lead::query()->create([
            ...$validated,
            'team_id' => TenantContext::getTeamId(),
            'owner_id' => null,
        ]);

        if ($hasOwnerId && $ownerId !== null) {
            \Gate::authorize('transfer', $lead);
            $ownership->assign(
                $lead,
                User::query()->find((int) $ownerId),
                $request->user(),
                'manual',
                null,
                false,
            );
        }

        LeadCreated::dispatch($lead);

        return to_route('leads.show', $lead);
    }

    public function show(Lead $lead): Response
    {
        \Gate::authorize('view', $lead);

        $lead->load(['unit', 'owner', 'statusHistories.actor', 'assignmentHistories.fromOwner', 'assignmentHistories.toOwner', 'assignmentHistories.actor']);

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
            'units' => Unit::query()->forCurrentTeam()->visibleTo(request()->user())->get(['id', 'name']),
            'members' => $this->teamMembers(),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead, LeadOwnershipService $ownership): RedirectResponse
    {
        \Gate::authorize('update', $lead);

        $validated = $request->validated();
        $hasOwnerId = array_key_exists('owner_id', $validated);
        $ownerId = $validated['owner_id'] ?? null;
        unset($validated['owner_id']);

        $lead->update($validated);

        if ($hasOwnerId && (int) $ownerId !== $lead->owner_id) {
            \Gate::authorize('transfer', $lead);
            $ownership->assign(
                $lead,
                $ownerId !== null ? User::query()->find((int) $ownerId) : null,
                $request->user(),
                'manual',
                $lead->getOriginal('owner_id') !== null ? User::query()->find((int) $lead->getOriginal('owner_id')) : null,
                true,
            );
        }

        return to_route('leads.show', $lead);
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        \Gate::authorize('delete', $lead);

        $lead->delete();

        return to_route('leads.index');
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function teamMembers(): array
    {
        $team = TenantContext::currentTeam();

        if ($team === null) {
            return [];
        }

        return $team->members()
            ->orderBy('name')
            ->get(['users.id', 'users.name'])
            ->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
            ])
            ->all();
    }
}
