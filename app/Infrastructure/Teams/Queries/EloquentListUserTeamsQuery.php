<?php

namespace App\Infrastructure\Teams\Queries;

use App\Application\Teams\Presenters\UserTeamPresenter;
use App\Application\Teams\Queries\ListUserTeamsQueryInterface;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;

class EloquentListUserTeamsQuery implements ListUserTeamsQueryInterface
{
    public function __construct(
        private readonly UserTeamPresenter $userTeamPresenter,
    ) {}

    public function forUser(User $user, bool $includeCurrent = true): Collection
    {
        return $user->teams()
            ->get()
            ->map(fn (Team $team) => ! $includeCurrent && $user->isCurrentTeam($team)
                ? null
                : $this->userTeamPresenter->present($user, $team))
            ->filter()
            ->values();
    }
}
