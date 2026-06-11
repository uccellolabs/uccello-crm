<?php

namespace App\Http\Controllers\Crm;

use App\Application\Admin\UseCases\ReorderByIds;
use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Domain\Shared\ValueObjects\UniqueSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\ReorderPipelineStagesRequest;
use App\Http\Requests\Crm\StorePipelineStageRequest;
use App\Http\Requests\Crm\UpdatePipelineStageRequest;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin screen for the sales pipelines: rename/recolor stages, reorder them,
 * add new ones and remove empty non-terminal ones. Won/lost flags are
 * structural (kanban + deal status depend on them) and stay immutable.
 */
class PipelineSettingsController extends Controller
{
    public function __construct(
        private readonly PipelineRepositoryInterface $pipelines,
        private readonly ReorderByIds $reorderByIds,
    ) {}

    /**
     * Display the pipeline administration screen.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('manage-pipelines');

        $this->pipelines->ensureDefaultExists($request->user()->currentTeam->id);

        $pipelines = Pipeline::query()
            ->with(['stages' => fn ($query) => $query->withCount('deals')])
            ->orderBy('position')
            ->get()
            ->map(fn (Pipeline $pipeline) => [
                'id' => $pipeline->id,
                'name' => $pipeline->name,
                'stages' => $pipeline->stages->map(fn (PipelineStage $stage) => [
                    'id' => $stage->id,
                    'name' => $stage->name,
                    'color' => $stage->color,
                    'position' => $stage->position,
                    'probability' => $stage->probability,
                    'is_won' => $stage->is_won,
                    'is_lost' => $stage->is_lost,
                    'deals_count' => $stage->deals_count,
                ])->values()->all(),
            ]);

        return Inertia::render('crm/settings/Pipeline', [
            'pipelines' => $pipelines,
        ]);
    }

    /**
     * Add a stage to a pipeline, inserted before the terminal stages.
     */
    public function storeStage(StorePipelineStageRequest $request): RedirectResponse
    {
        Gate::authorize('manage-pipelines');

        $validated = $request->validated();
        $pipeline = Pipeline::query()->findOrFail((int) $validated['pipeline_id']);

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
            $validated['name'],
            fn (string $slug) => $pipeline->stages()->where('key', $slug)->exists(),
            'stage',
        );

        $stage = $pipeline->stages()->make([
            'name' => $validated['name'],
            'key' => $key->value,
            'color' => $validated['color'] ?? '#2740e0',
            'position' => $position,
            'probability' => $validated['probability'] ?? 50,
            'is_won' => false,
            'is_lost' => false,
        ]);
        $stage->team_id = $pipeline->team_id;
        $stage->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Stage added.')]);

        return back();
    }

    /**
     * Rename, recolor or reweight a stage.
     */
    public function updateStage(UpdatePipelineStageRequest $request, PipelineStage $stage): RedirectResponse
    {
        Gate::authorize('manage-pipelines');

        $validated = $request->validated();

        $stage->update([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? $stage->color,
            'probability' => $validated['probability'] ?? $stage->probability,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Stage updated.')]);

        return back();
    }

    /**
     * Remove an empty, non-terminal stage.
     */
    public function destroyStage(PipelineStage $stage): RedirectResponse
    {
        Gate::authorize('manage-pipelines');

        if ($stage->is_won || $stage->is_lost) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Terminal stages cannot be deleted.')]);

            return back();
        }

        if ($stage->deals()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Move the deals out of this stage first.')]);

            return back();
        }

        $stage->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Stage deleted.')]);

        return back();
    }

    /**
     * Persist a new ordering of stages within a pipeline.
     */
    public function reorderStages(ReorderPipelineStagesRequest $request): RedirectResponse
    {
        Gate::authorize('manage-pipelines');

        $this->reorderByIds->handle(PipelineStage::class, $request->orderedIds());

        return back();
    }
}
