<?php

namespace App\Application\Teams\UseCases;

use App\Application\Teams\Commands\UpdateMemberCommand;
use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Models\Team;
use App\Models\User;

class UpdateMember
{
    public function __construct(
        private readonly TeamRepositoryInterface $teams,
    ) {}

    public function handle(Team $team, User $member, UpdateMemberCommand $command): void
    {
        $this->teams->updateMemberRole($team, $member, $command->role);
    }
}
