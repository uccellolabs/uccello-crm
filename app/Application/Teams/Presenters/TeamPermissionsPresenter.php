<?php

namespace App\Application\Teams\Presenters;

use App\Application\Teams\DTOs\TeamPermissions;
use App\Domain\Shared\Enums\TeamPermission;
use App\Models\Team;
use App\Models\User;

class TeamPermissionsPresenter
{
    public function present(User $user, Team $team): TeamPermissions
    {
        $role = $user->teamRole($team);

        return new TeamPermissions(
            canUpdateTeam: $role?->hasPermission(TeamPermission::UpdateTeam) ?? false,
            canDeleteTeam: $role?->hasPermission(TeamPermission::DeleteTeam) ?? false,
            canAddMember: $role?->hasPermission(TeamPermission::AddMember) ?? false,
            canUpdateMember: $role?->hasPermission(TeamPermission::UpdateMember) ?? false,
            canRemoveMember: $role?->hasPermission(TeamPermission::RemoveMember) ?? false,
            canCreateInvitation: $role?->hasPermission(TeamPermission::CreateInvitation) ?? false,
            canCancelInvitation: $role?->hasPermission(TeamPermission::CancelInvitation) ?? false,
        );
    }
}
