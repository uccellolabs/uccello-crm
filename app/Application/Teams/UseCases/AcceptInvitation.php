<?php

namespace App\Application\Teams\UseCases;

use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Domain\Teams\Services\TeamInvitationAcceptancePolicy;
use App\Models\TeamInvitation;
use App\Models\User;

class AcceptInvitation
{
    public function __construct(
        private readonly TeamRepositoryInterface $teams,
        private readonly TeamInvitationAcceptancePolicy $policy,
    ) {}

    public function handle(User $user, TeamInvitation $invitation): void
    {
        abort_unless($this->policy->canAccept($invitation, $user->email), 403);

        $this->teams->acceptInvitationAndSwitch($user, $invitation);
    }
}
