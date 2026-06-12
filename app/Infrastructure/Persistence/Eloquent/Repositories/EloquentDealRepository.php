<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Domain\Deals\ValueObjects\DealClosure;
use App\Models\Deal;
use Illuminate\Support\Facades\DB;

class EloquentDealRepository implements DealRepositoryInterface
{
    public function create(array $data): Deal
    {
        return Deal::create($data);
    }

    public function update(Deal $deal, array $data): Deal
    {
        $deal->update($data);

        return $deal->fresh();
    }

    public function delete(Deal $deal): void
    {
        $deal->delete();
    }

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

    public function moveToStage(Deal $deal, int $stageId, bool $isWon, bool $isLost, int $position): Deal
    {
        return DB::transaction(function () use ($deal, $stageId, $isWon, $isLost, $position) {
            $fromStageId = $deal->pipeline_stage_id;

            $closure = DealClosure::fromTerminalFlags($isWon, $isLost, $deal->closed_at);
            $attributes = $closure->toModelAttributes();

            $deal->update([
                'pipeline_stage_id' => $stageId,
                'status' => $attributes['status'],
                'closed_at' => $attributes['closed_at'],
            ]);

            $updated = $deal->fresh();

            $this->resequence($stageId, $updated->id, $position);

            if ($fromStageId !== $stageId) {
                $this->resequence($fromStageId);
            }

            return $updated;
        });
    }
}
