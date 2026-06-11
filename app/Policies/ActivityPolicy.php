<?php

namespace App\Policies;

use App\Domain\Shared\Enums\TeamPermission;
use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    /**
     * Determine whether the user can create activities.
     */
    public function create(User $user): bool
    {
        return $user->currentTeam !== null
            && $user->hasTeamPermission($user->currentTeam, TeamPermission::ManageCrmRecords);
    }

    /**
     * Determine whether the user can delete the activity.
     */
    public function delete(User $user, Activity $activity): bool
    {
        return $user->hasTeamPermission($activity->team, TeamPermission::ManageCrmRecords);
    }
}
