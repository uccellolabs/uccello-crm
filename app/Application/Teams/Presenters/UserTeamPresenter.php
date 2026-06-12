<?php

namespace App\Application\Teams\Presenters;

use App\Application\Shared\Presenters\EnumLabels;
use App\Application\Teams\DTOs\UserTeam;
use App\Models\Team;
use App\Models\User;

class UserTeamPresenter
{
    public function present(User $user, Team $team, ?bool $isCurrent = null): UserTeam
    {
        $role = $user->teamRole($team);

        return new UserTeam(
            id: $team->id,
            name: $team->name,
            slug: $team->slug,
            isPersonal: $team->is_personal,
            role: $role?->value,
            roleLabel: $role !== null ? EnumLabels::teamRole($role) : null,
            isCurrent: $isCurrent ?? $user->isCurrentTeam($team),
        );
    }
}
