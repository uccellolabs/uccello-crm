<?php

namespace App\Policies;

use App\Domain\Shared\Enums\TeamPermission;
use App\Models\CustomFieldDefinition;
use App\Models\User;

class CustomFieldDefinitionPolicy
{
    /**
     * Only team admins/owners manage custom field definitions.
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

    public function update(User $user, CustomFieldDefinition $definition): bool
    {
        return $user->hasTeamPermission($definition->team, TeamPermission::ManageCustomFields);
    }

    public function delete(User $user, CustomFieldDefinition $definition): bool
    {
        return $user->hasTeamPermission($definition->team, TeamPermission::ManageCustomFields);
    }
}
