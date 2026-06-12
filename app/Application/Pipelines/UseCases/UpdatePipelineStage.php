<?php

namespace App\Application\Pipelines\UseCases;

use App\Application\Pipelines\Commands\UpdatePipelineStageCommand;
use App\Domain\Pipelines\Repositories\PipelineStageRepositoryInterface;
use App\Models\PipelineStage;

class UpdatePipelineStage
{
    public function __construct(
        private readonly PipelineStageRepositoryInterface $stages,
    ) {}

    public function handle(PipelineStage $stage, UpdatePipelineStageCommand $command): PipelineStage
    {
        return $this->stages->update($stage, $command->toArray());
    }
}
