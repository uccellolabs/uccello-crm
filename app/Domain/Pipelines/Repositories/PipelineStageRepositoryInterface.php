<?php

namespace App\Domain\Pipelines\Repositories;

use App\Models\Pipeline;
use App\Models\PipelineStage;

interface PipelineStageRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Pipeline $pipeline, array $data): PipelineStage;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PipelineStage $stage, array $data): PipelineStage;

    public function delete(PipelineStage $stage): void;

    public function hasDeals(int $stageId): bool;

    /**
     * @param  list<int>  $ids
     */
    public function reorder(array $ids): void;
}
