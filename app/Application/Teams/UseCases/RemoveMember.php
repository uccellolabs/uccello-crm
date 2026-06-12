<?php

namespace App\Application\Teams\UseCases;

use App\Application\Shared\Results\OperationResult;
use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Domain\Teams\Services\TeamMemberRemovalPolicy;
use App\Models\Team;
use App\Models\User;

class RemoveMember
{
    public function __construct(
        private readonly TeamRepositoryInterface $teams,
        private readonly TeamMemberRemovalPolicy $policy,
    ) {}

    public function handle(Team $team, User $member): OperationResult
    {
        if (! $this->policy->canRemove($team, $member)) {
            return OperationResult::NotAllowed;
        }

        $this->teams->removeMember($team, $member);

        if ($member->isCurrentTeam($team)) {
            $member->switchTeam($member->personalTeam());
        }

        return OperationResult::Success;
    }
}
