<?php

namespace App\Http\Controllers\Teams;

use App\Application\Teams\Queries\GetTeamEditPageQueryInterface;
use App\Application\Teams\Queries\ListUserTeamsQueryInterface;
use App\Application\Teams\UseCases\CreateTeam;
use App\Application\Teams\UseCases\DeleteTeam;
use App\Application\Teams\UseCases\SwitchTeam;
use App\Application\Teams\UseCases\UpdateTeam;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\DeleteTeamRequest;
use App\Http\Requests\Teams\SaveTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(Request $request, ListUserTeamsQueryInterface $listUserTeams): Response
    {
        return Inertia::render('teams/Index', [
            'teams' => $listUserTeams->forUser($request->user(), includeCurrent: true),
        ]);
    }

    public function store(SaveTeamRequest $request, CreateTeam $createTeam): RedirectResponse
    {
        $team = $createTeam->handle($request->user(), $request->toCreateCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team created.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    public function edit(Request $request, Team $team, GetTeamEditPageQueryInterface $getTeamEditPage): Response
    {
        $page = $getTeamEditPage->forUserAndTeam($request->user(), $team);

        return Inertia::render('teams/Edit', [
            'team' => $page->team,
            'members' => $page->members,
            'invitations' => $page->invitations,
            'permissions' => $page->permissions,
            'availableRoles' => $page->availableRoles,
        ]);
    }

    public function update(SaveTeamRequest $request, Team $team, UpdateTeam $updateTeam): RedirectResponse
    {
        Gate::authorize('update', $team);

        $team = $updateTeam->handle($team, $request->toUpdateCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team updated.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    public function switch(Request $request, Team $team, SwitchTeam $switchTeam): RedirectResponse
    {
        $switchTeam->handle($request->user(), $team);

        return back();
    }

    public function destroy(DeleteTeamRequest $request, Team $team, DeleteTeam $deleteTeam): RedirectResponse
    {
        $deleteTeam->handle($request->user(), $team);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team deleted.')]);

        return to_route('teams.index');
    }
}
