<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Models\Deal;

class EloquentDealRepository implements DealRepositoryInterface
{
    public function nextPosition(int $stageId): int
    {
        return (int) Deal::query()->where('pipeline_stage_id', $stageId)->max('position') + 1;
    }

    public function resequence(int $stageId, ?int $dealId = null, ?int $position = null): void
    {
        $ids = Deal::query()
            ->where('pipeline_stage_id', $stageId)
            ->orderBy('position')
            ->orderBy('id')
            ->pluck('id')
            ->reject(fn (int $id) => $id === $dealId)
            ->values();

        if ($dealId !== null) {
            $index = max(0, min($position ?? $ids->count(), $ids->count()));
            $ids->splice($index, 0, [$dealId]);
        }

        $ids->each(function (int $id, int $index) {
            Deal::query()->whereKey($id)->update(['position' => $index]);
        });
    }
}
