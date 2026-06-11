<?php

namespace App\Policies;

use App\Domain\Shared\Enums\TeamPermission;
use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentTeam !== null
            && $user->hasTeamPermission($user->currentTeam, TeamPermission::ViewCrm);
    }

    public function view(User $user, Deal $deal): bool
    {
        return $user->hasTeamPermission($deal->team, TeamPermission::ViewCrm);
    }

    public function create(User $user): bool
    {
        return $user->currentTeam !== null
            && $user->hasTeamPermission($user->currentTeam, TeamPermission::ManageCrmRecords);
    }

    public function update(User $user, Deal $deal): bool
    {
        return $user->hasTeamPermission($deal->team, TeamPermission::ManageCrmRecords);
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $user->hasTeamPermission($deal->team, TeamPermission::ManageCrmRecords);
    }
}
