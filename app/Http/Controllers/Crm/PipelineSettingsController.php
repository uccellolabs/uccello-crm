<?php

namespace App\Http\Controllers\Crm;

use App\Application\Pipelines\Queries\GetPipelineSettingsQueryInterface;
use App\Application\Pipelines\Repositories\PipelineReadRepositoryInterface;
use App\Application\Pipelines\UseCases\CreatePipelineStage;
use App\Application\Pipelines\UseCases\DeletePipelineStage;
use App\Application\Pipelines\UseCases\ReorderPipelineStages;
use App\Application\Pipelines\UseCases\UpdatePipelineStage;
use App\Application\Shared\Results\DeletionResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\ReorderPipelineStagesRequest;
use App\Http\Requests\Crm\StorePipelineStageRequest;
use App\Http\Requests\Crm\UpdatePipelineStageRequest;
use App\Models\PipelineStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PipelineSettingsController extends Controller
{
    public function __construct(
        private readonly PipelineReadRepositoryInterface $pipelineRead,
        private readonly GetPipelineSettingsQueryInterface $pipelineSettings,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('manage-pipelines');

        return Inertia::render('crm/settings/Pipeline', [
            'pipelines' => $this->pipelineSettings->all()->pipelines,
        ]);
    }

    public function storeStage(StorePipelineStageRequest $request, CreatePipelineStage $createStage): RedirectResponse
    {
        Gate::authorize('manage-pipelines');

        $command = $request->toCommand();
        $pipeline = $this->pipelineRead->findOrFail($command->pipelineId);

        $createStage->handle($pipeline, $command);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Stage added.')]);

        return back();
    }

    public function updateStage(UpdatePipelineStageRequest $request, PipelineStage $stage, UpdatePipelineStage $updateStage): RedirectResponse
    {
        Gate::authorize('manage-pipelines');

        $updateStage->handle($stage, $request->toCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Stage updated.')]);

        return back();
    }

    public function destroyStage(PipelineStage $stage, DeletePipelineStage $deleteStage): RedirectResponse
    {
        Gate::authorize('manage-pipelines');

        $result = $deleteStage->handle($stage);

        Inertia::flash('toast', [
            'type' => $result->succeeded() ? 'success' : 'error',
            'message' => match ($result) {
                DeletionResult::Success => __('Stage deleted.'),
                DeletionResult::BlockedTerminalStage => __('Terminal stages cannot be deleted.'),
                DeletionResult::BlockedHasDeals => __('Move the deals out of this stage first.'),
            },
        ]);

        return back();
    }

    public function reorderStages(ReorderPipelineStagesRequest $request, ReorderPipelineStages $reorderStages): RedirectResponse
    {
        Gate::authorize('manage-pipelines');

        $reorderStages->handle($request->toCommand());

        return back();
    }
}
