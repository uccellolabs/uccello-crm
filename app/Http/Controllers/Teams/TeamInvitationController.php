<?php

namespace App\Http\Controllers\Teams;

use App\Application\Teams\UseCases\AcceptInvitation;
use App\Application\Teams\UseCases\CancelInvitation;
use App\Application\Teams\UseCases\InviteMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\AcceptTeamInvitationRequest;
use App\Http\Requests\Teams\CreateTeamInvitationRequest;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TeamInvitationController extends Controller
{
    public function store(
        CreateTeamInvitationRequest $request,
        Team $team,
        InviteMember $inviteMember,
    ): RedirectResponse {
        Gate::authorize('inviteMember', $team);

        $inviteMember->handle($team, $request->user(), $request->toCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invitation sent.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    public function destroy(
        Team $team,
        TeamInvitation $invitation,
        CancelInvitation $cancelInvitation,
    ): RedirectResponse {
        abort_unless($invitation->team_id === $team->id, 404);

        Gate::authorize('cancelInvitation', $team);

        $cancelInvitation->handle($invitation);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invitation cancelled.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    public function accept(
        AcceptTeamInvitationRequest $request,
        TeamInvitation $invitation,
        AcceptInvitation $acceptInvitation,
    ): RedirectResponse {
        $acceptInvitation->handle($request->user(), $invitation);

        return to_route('dashboard');
    }
}
