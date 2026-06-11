<?php

namespace App\Domain\Deals\Repositories;

interface DealRepositoryInterface
{
    public function nextPosition(int $stageId): int;

    public function resequence(int $stageId, ?int $dealId = null, ?int $position = null): void;
}
