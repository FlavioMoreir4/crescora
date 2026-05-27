<?php

namespace App\Http\Controllers\Teams;

use App\Domains\Teams\Models\TeamResourceAccess;
use App\Enums\TeamResourceAccessLevel;
use App\Enums\TeamResourceType;
use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\UpdateTeamMemberRequest;
use App\Http\Requests\Teams\UpdateTeamMemberResourceAccessRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TeamMemberController extends Controller
{
    /**
     * Update the specified team member's role.
     */
    public function update(UpdateTeamMemberRequest $request, Team $team, User $user): RedirectResponse
    {
        Gate::authorize('updateMember', $team);

        $newRole = TeamRole::from($request->validated('role'));

        $team->memberships()
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->update(['role' => $newRole]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Member role updated.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    /**
     * Remove the specified team member.
     */
    public function destroy(Team $team, User $user): RedirectResponse
    {
        Gate::authorize('removeMember', $team);

        abort_if($team->owner()?->is($user), 403, __('The team owner cannot be removed.'));

        $team->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($user->isCurrentTeam($team)) {
            $user->switchTeam($user->personalTeam());
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Member removed.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    /**
     * Update the member's explicit resource access allowlist.
     */
    public function updateResourceAccess(
        UpdateTeamMemberResourceAccessRequest $request,
        Team $team,
        User $user,
    ): RedirectResponse {
        Gate::authorize('updateMember', $team);

        abort_if($team->owner()?->is($user), 403, __('The team owner already has full access.'));

        DB::transaction(function () use ($request, $team, $user): void {
            TeamResourceAccess::query()
                ->where('team_id', $team->id)
                ->where('user_id', $user->id)
                ->delete();

            $payload = [];

            foreach (['units' => TeamResourceType::Unit, 'forms' => TeamResourceType::Form] as $key => $resourceType) {
                foreach (Arr::get($request->validated(), "resources.{$key}", []) as $resource) {
                    $accessLevel = $resource['access_level'] ?? null;

                    if ($accessLevel === null || $accessLevel === '') {
                        continue;
                    }

                    $payload[] = [
                        'team_id' => $team->id,
                        'user_id' => $user->id,
                        'resource_type' => $resourceType->value,
                        'resource_id' => (int) $resource['resource_id'],
                        'access_level' => TeamResourceAccessLevel::from($accessLevel)->value,
                        'granted_by' => $request->user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if ($payload !== []) {
                TeamResourceAccess::query()->insert($payload);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Member access updated.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }
}
