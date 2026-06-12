<?php

namespace App\Application\Teams\Queries;

use App\Application\Teams\DTOs\TeamEditPageData;
use App\Models\Team;
use App\Models\User;

interface GetTeamEditPageQueryInterface
{
    public function forUserAndTeam(User $user, Team $team): TeamEditPageData;
}
