<?php

namespace App\Domain\Deals\Repositories;

use App\Models\Deal;

interface DealRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Deal;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Deal $deal, array $data): Deal;

    public function delete(Deal $deal): void;

    public function nextPosition(int $stageId): int;

    public function resequence(int $stageId, ?int $dealId = null, ?int $position = null): void;

    public function moveToStage(Deal $deal, int $stageId, bool $isWon, bool $isLost, int $position): Deal;
}
