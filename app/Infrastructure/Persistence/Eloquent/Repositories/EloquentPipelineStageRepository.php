<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Pipelines\Repositories\PipelineStageRepositoryInterface;
use App\Domain\Shared\Exceptions\InvalidReorderException;
use App\Domain\Shared\ValueObjects\UniqueSlug;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Model;

class EloquentPipelineStageRepository implements PipelineStageRepositoryInterface
{
    public function create(Pipeline $pipeline, array $data): PipelineStage
    {
        $firstTerminalPosition = $pipeline->stages()
            ->where(fn ($query) => $query->where('is_won', true)->orWhere('is_lost', true))
            ->min('position');

        $position = $firstTerminalPosition !== null
            ? (int) $firstTerminalPosition
            : (int) $pipeline->stages()->max('position') + 1;

        $pipeline->stages()
            ->where('position', '>=', $position)
            ->increment('position');

        $key = UniqueSlug::generate(
            $data['name'],
            fn (string $slug) => $pipeline->stages()->where('key', $slug)->exists(),
            'stage',
        );

        $stage = $pipeline->stages()->make([
            'name' => $data['name'],
            'key' => $key->value,
            'color' => $data['color'] ?? '#2740e0',
            'position' => $position,
            'probability' => $data['probability'] ?? 50,
            'is_won' => false,
            'is_lost' => false,
        ]);
        $stage->team_id = $pipeline->team_id;
        $stage->save();

        return $stage;
    }

    public function update(PipelineStage $stage, array $data): PipelineStage
    {
        $stage->update([
            'name' => $data['name'],
            'color' => $data['color'] ?? $stage->color,
            'probability' => $data['probability'] ?? $stage->probability,
        ]);

        return $stage->fresh();
    }

    public function delete(PipelineStage $stage): void
    {
        $stage->delete();
    }

    public function hasDeals(int $stageId): bool
    {
        return PipelineStage::query()->find($stageId)?->deals()->exists() ?? false;
    }

    public function reorder(array $ids): void
    {
        $stages = PipelineStage::query()->whereIn('id', $ids)->get();

        if ($stages->count() !== count($ids)) {
            throw InvalidReorderException::idCountMismatch();
        }

        $stages->each(fn (Model $stage) => $stage->update([
            'position' => array_search($stage->getKey(), $ids, true),
        ]));
    }
}
