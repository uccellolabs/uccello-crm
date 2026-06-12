<?php

namespace App\Application\Pipelines\UseCases;

use App\Application\Pipelines\Commands\CreatePipelineStageCommand;
use App\Domain\Pipelines\Repositories\PipelineStageRepositoryInterface;
use App\Models\Pipeline;
use App\Models\PipelineStage;

class CreatePipelineStage
{
    public function __construct(
        private readonly PipelineStageRepositoryInterface $stages,
    ) {}

    public function handle(Pipeline $pipeline, CreatePipelineStageCommand $command): PipelineStage
    {
        return $this->stages->create($pipeline, $command->toArray());
    }
}
