<?php

namespace App\Http\Controllers\Teams;

use App\Application\Shared\Results\OperationResult;
use App\Application\Teams\UseCases\RemoveMember;
use App\Application\Teams\UseCases\UpdateMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\UpdateTeamMemberRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TeamMemberController extends Controller
{
    public function update(
        UpdateTeamMemberRequest $request,
        Team $team,
        User $user,
        UpdateMember $updateMember,
    ): RedirectResponse {
        Gate::authorize('updateMember', $team);

        $updateMember->handle($team, $user, $request->toCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Member role updated.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    public function destroy(Team $team, User $user, RemoveMember $removeMember): RedirectResponse
    {
        Gate::authorize('removeMember', $team);

        $result = $removeMember->handle($team, $user);

        if ($result === OperationResult::NotAllowed) {
            abort(403);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Member removed.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }
}
