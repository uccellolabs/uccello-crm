<?php

namespace App\Application\Teams\UseCases;

use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Models\TeamInvitation;

class CancelInvitation
{
    public function __construct(
        private readonly TeamRepositoryInterface $teams,
    ) {}

    public function handle(TeamInvitation $invitation): void
    {
        $this->teams->deleteInvitation($invitation);
    }
}
