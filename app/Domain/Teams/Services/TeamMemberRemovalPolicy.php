<?php

namespace App\Domain\Teams\Services;

use App\Models\Team;
use App\Models\User;

final class TeamMemberRemovalPolicy
{
    public function canRemove(Team $team, User $member): bool
    {
        return ! $team->owner()?->is($member);
    }
}
