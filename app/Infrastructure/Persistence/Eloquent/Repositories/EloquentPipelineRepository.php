<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Models\Pipeline;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EloquentPipelineRepository implements PipelineRepositoryInterface
{
    /**
     * @var list<array<string, mixed>>
     */
    private const STAGES = [
        ['name' => 'Lead', 'key' => 'lead', 'color' => '#8b5cf6', 'probability' => 10],
        ['name' => 'Qualifié', 'key' => 'qualified', 'color' => '#06b6d4', 'probability' => 30],
        ['name' => 'Proposition', 'key' => 'proposal', 'color' => '#2740e0', 'probability' => 50],
        ['name' => 'Négociation', 'key' => 'negotiation', 'color' => '#f59e0b', 'probability' => 75],
        ['name' => 'Gagné', 'key' => 'won', 'color' => '#10b981', 'probability' => 100, 'is_won' => true],
        ['name' => 'Perdu', 'key' => 'lost', 'color' => '#f43f5e', 'probability' => 0, 'is_lost' => true],
    ];

    public function ensureDefaultExists(int $teamId): void
    {
        if (Pipeline::query()->exists()) {
            return;
        }

        Cache::lock("default-pipeline:{$teamId}", 10)->block(10, function () use ($teamId) {
            if (! Pipeline::query()->exists()) {
                $this->createDefault($teamId);
            }
        });
    }

    public function createDefault(int $teamId): Pipeline
    {
        $team = Team::query()->findOrFail($teamId);

        return DB::transaction(function () use ($team) {
            $pipeline = new Pipeline(['name' => 'Pipeline commercial', 'is_default' => true, 'position' => 0]);
            $pipeline->team_id = $team->id;
            $pipeline->save();

            foreach (self::STAGES as $position => $stage) {
                $model = $pipeline->stages()->make([
                    'name' => $stage['name'],
                    'key' => $stage['key'],
                    'color' => $stage['color'],
                    'position' => $position,
                    'is_won' => $stage['is_won'] ?? false,
                    'is_lost' => $stage['is_lost'] ?? false,
                    'probability' => $stage['probability'],
                ]);
                $model->team_id = $team->id;
                $model->save();
            }

            return $pipeline;
        });
    }
}
