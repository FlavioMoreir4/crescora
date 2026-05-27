<?php

namespace App\Http\Controllers\Teams;

use App\Actions\Teams\CreateTeam;
use App\Enums\TeamRole;
use App\Enums\TeamResourceAccessLevel;
use App\Enums\TeamResourceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\DeleteTeamRequest;
use App\Http\Requests\Teams\SaveTeamRequest;
use App\Domains\Forms\Models\Form;
use App\Domains\Units\Models\Unit;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    /**
     * Display a listing of the user's teams.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('teams/Index', [
            'teams' => $user->toUserTeams(includeCurrent: true),
        ]);
    }

    /**
     * Store a newly created team.
     */
    public function store(SaveTeamRequest $request, CreateTeam $createTeam): RedirectResponse
    {
        Gate::authorize('create', Team::class);

        $team = $createTeam->handle($request->user(), $request->validated('name'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team created.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    /**
     * Show the team edit page.
     */
    public function edit(Request $request, Team $team): Response
    {
        Gate::authorize('update', $team);

        $user = $request->user();
        $units = Unit::query()
            ->where('team_id', $team->id)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
        $forms = Form::query()
            ->where('team_id', $team->id)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'config']);
        $resourceAccesses = $team->resourceAccesses()
            ->get()
            ->groupBy('user_id');

        return Inertia::render('teams/Edit', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'isPersonal' => $team->is_personal,
            ],
            'members' => $team->members()->get()->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'avatar' => $member->avatar ?? null,
                'role' => $member->pivot->role->value,
                'role_label' => $member->pivot->role->label(),
                'resource_accesses' => $resourceAccesses->get($member->id, collect())
                    ->map(fn ($access) => [
                        'resource_type' => $access->resource_type->value,
                        'resource_id' => $access->resource_id,
                        'access_level' => $access->access_level->value,
                    ])
                    ->values(),
            ]),
            'invitations' => $team->invitations()
                ->whereNull('accepted_at')
                ->get()
                ->map(fn ($invitation) => [
                    'code' => $invitation->code,
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                'role_label' => $invitation->role->label(),
                'created_at' => $invitation->created_at->toISOString(),
            ]),
            'permissions' => $user->toTeamPermissions($team),
            'availableRoles' => TeamRole::assignable(),
            'resourceAccessLevels' => TeamResourceAccessLevel::options(),
            'units' => $units->map(fn ($unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'slug' => $unit->slug,
            ]),
            'forms' => $forms->map(fn ($form) => [
                'id' => $form->id,
                'name' => $form->name,
                'slug' => $form->slug,
                'leadAssignment' => [
                    'mode' => data_get($form->config, 'lead_assignment.mode', 'distribution'),
                    'owner_id' => data_get($form->config, 'lead_assignment.owner_id'),
                ],
            ]),
        ]);
    }

    /**
     * Update the specified team.
     */
    public function update(SaveTeamRequest $request, Team $team): RedirectResponse
    {
        Gate::authorize('update', $team);

        $team = DB::transaction(function () use ($request, $team) {
            $team = Team::whereKey($team->id)->lockForUpdate()->firstOrFail();

            $team->update(['name' => $request->validated('name')]);

            return $team;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team updated.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    /**
     * Switch the user's current team.
     */
    public function switch(Request $request, Team $team): RedirectResponse
    {
        abort_unless($request->user()->belongsToTeam($team), 403);

        $request->user()->switchTeam($team);

        return back();
    }

    /**
     * Delete the specified team.
     */
    public function destroy(DeleteTeamRequest $request, Team $team): RedirectResponse
    {
        $user = $request->user();
        $fallbackTeam = $user->isCurrentTeam($team)
            ? $user->fallbackTeam($team)
            : null;

        DB::transaction(function () use ($user, $team) {
            User::where('current_team_id', $team->id)
                ->where('id', '!=', $user->id)
                ->each(fn (User $affectedUser) => $affectedUser->switchTeam($affectedUser->personalTeam()));

            $team->invitations()->delete();
            $team->memberships()->delete();
            $team->delete();
        });

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team deleted.')]);

        return to_route('teams.index');
    }
}
