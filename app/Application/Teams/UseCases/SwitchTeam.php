<?php

namespace App\Application\Teams\UseCases;

use App\Models\Team;
use App\Models\User;

class SwitchTeam
{
    public function handle(User $user, Team $team): void
    {
        abort_unless($user->belongsToTeam($team), 403);

        $user->switchTeam($team);
    }
}
