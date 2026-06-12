<?php

namespace App\Application\Teams\UseCases;

use App\Application\Teams\Commands\InviteMemberCommand;
use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

class InviteMember
{
    public function __construct(
        private readonly TeamRepositoryInterface $teams,
    ) {}

    public function handle(Team $team, User $inviter, InviteMemberCommand $command): TeamInvitation
    {
        return $this->teams->createInvitation(
            $team,
            $command->email,
            $command->role,
            $inviter->id,
            now()->addDays(3),
        );
    }
}
