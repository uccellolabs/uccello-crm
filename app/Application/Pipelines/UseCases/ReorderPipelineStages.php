<?php

namespace App\Application\Pipelines\UseCases;

use App\Application\Shared\Commands\ReorderIdsCommand;
use App\Domain\Pipelines\Repositories\PipelineStageRepositoryInterface;

class ReorderPipelineStages
{
    public function __construct(
        private readonly PipelineStageRepositoryInterface $stages,
    ) {}

    public function handle(ReorderIdsCommand $command): void
    {
        $this->stages->reorder($command->ids);
    }
}
