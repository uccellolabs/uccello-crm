<?php

namespace App\Application\Teams\UseCases;

use App\Application\Pipelines\UseCases\CreateDefaultPipeline;
use App\Application\Teams\Commands\CreateTeamCommand;
use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Models\Team;
use App\Models\User;

class CreateTeam
{
    public function __construct(
        private readonly TeamRepositoryInterface $teams,
        private readonly CreateDefaultPipeline $createDefaultPipeline,
    ) {}

    public function handle(User $user, CreateTeamCommand $command): Team
    {
        $team = $this->teams->createForUser($user, $command->name, $command->isPersonal);

        $this->createDefaultPipeline->handle($team);

        return $team;
    }
}
