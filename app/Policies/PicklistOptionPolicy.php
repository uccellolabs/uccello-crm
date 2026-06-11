<?php

namespace App\Policies;

use App\Domain\Shared\Enums\TeamPermission;
use App\Models\PicklistOption;
use App\Models\User;

class PicklistOptionPolicy
{
    /**
     * Picklists share the settings permission with custom fields: only team
     * admins/owners manage them.
     */
    public function manage(User $user): bool
    {
        return $user->currentTeam !== null
            && $user->hasTeamPermission($user->currentTeam, TeamPermission::ManageCustomFields);
    }

    public function viewAny(User $user): bool
    {
        return $this->manage($user);
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, PicklistOption $option): bool
    {
        return $user->hasTeamPermission($option->team, TeamPermission::ManageCustomFields);
    }

    public function delete(User $user, PicklistOption $option): bool
    {
        return $user->hasTeamPermission($option->team, TeamPermission::ManageCustomFields)
            && ! $option->is_system;
    }
}
