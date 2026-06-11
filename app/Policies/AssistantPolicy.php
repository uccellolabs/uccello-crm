<?php

namespace App\Policies;

use App\Domain\Shared\Enums\TeamPermission;
use App\Models\User;

class AssistantPolicy
{
    /**
     * Determine whether the user can chat with the CRM assistant.
     */
    public function chat(User $user): bool
    {
        return $user->currentTeam !== null
            && $user->hasTeamPermission($user->currentTeam, TeamPermission::ViewCrm);
    }
}
