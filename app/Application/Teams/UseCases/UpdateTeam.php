<?php

namespace App\Application\Teams\UseCases;

use App\Application\Teams\Commands\UpdateTeamCommand;
use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Models\Team;

class UpdateTeam
{
    public function __construct(
        private readonly TeamRepositoryInterface $teams,
    ) {}

    public function handle(Team $team, UpdateTeamCommand $command): Team
    {
        return $this->teams->updateNameLocked($team, $command->name);
    }
}
