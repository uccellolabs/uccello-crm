<?php

namespace App\Application\Teams\UseCases;

use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Models\Team;
use App\Models\User;

class DeleteTeam
{
    public function __construct(
        private readonly TeamRepositoryInterface $teams,
    ) {}

    public function handle(User $user, Team $team): void
    {
        $fallbackTeam = $user->isCurrentTeam($team)
            ? $user->fallbackTeam($team)
            : null;

        $this->teams->purge($team, $user);

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }
    }
}
