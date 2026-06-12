<?php

namespace App\Application\Pipelines\UseCases;

use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Models\Pipeline;
use App\Models\Team;

class CreateDefaultPipeline
{
    public function __construct(
        private readonly PipelineRepositoryInterface $pipelines,
    ) {}

    /**
     * Create the default pipeline and its stages for a team.
     */
    public function handle(Team $team): Pipeline
    {
        return $this->pipelines->createDefault($team->id);
    }
}
