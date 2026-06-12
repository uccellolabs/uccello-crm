<?php

namespace App\Http\Controllers\Crm;

use App\Application\Crm\Queries\GetCrmRecordFormDataQueryInterface;
use App\Application\Deals\Queries\GetDealBoardQueryInterface;
use App\Application\Deals\Queries\GetDealShowPageQueryInterface;
use App\Application\Deals\UseCases\CreateDeal;
use App\Application\Deals\UseCases\DeleteDeal;
use App\Application\Deals\UseCases\MoveDeal;
use App\Application\Deals\UseCases\UpdateDeal;
use App\Application\Pipelines\Repositories\PipelineReadRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\MoveDealRequest;
use App\Http\Requests\Crm\StoreDealRequest;
use App\Http\Requests\Crm\UpdateDealRequest;
use App\Models\Deal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DealController extends Controller
{
    public function __construct(
        private readonly PipelineReadRepositoryInterface $pipelineRead,
        private readonly GetDealBoardQueryInterface $dealBoard,
        private readonly GetDealShowPageQueryInterface $dealShowPage,
        private readonly GetCrmRecordFormDataQueryInterface $formData,
    ) {}

    public function board(Request $request): Response
    {
        Gate::authorize('viewAny', Deal::class);

        $pipeline = $this->pipelineRead->resolveForRequest(
            $request->integer('pipeline') ?: null,
        );

        $board = $this->dealBoard->forPipeline($request->user(), $pipeline->id);

        return Inertia::render('crm/deals/Board', [
            'pipeline' => $board->pipeline,
            'pipelines' => $board->pipelines,
            'stages' => $board->stages,
            'can' => $board->can,
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Deal::class);

        $this->pipelineRead->resolveForRequest($request->integer('pipeline') ?: null);

        return Inertia::render('crm/deals/Create', $this->formData->forDealCreate(
            $request->user(),
            $request->integer('stage_id') ?: null,
            $request->integer('company_id') ?: null,
            $request->integer('contact_id') ?: null,
        ));
    }

    public function store(StoreDealRequest $request, CreateDeal $createDeal): RedirectResponse
    {
        Gate::authorize('create', Deal::class);

        $stage = $this->pipelineRead->findStageOrFail((int) $request->validated('pipeline_stage_id'));
        $deal = $createDeal->handle($request->toCommand(), $stage);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Deal created.')]);

        return to_route('deals.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'deal' => $deal->id,
        ]);
    }

    public function show(Request $request, Deal $deal): Response
    {
        Gate::authorize('view', $deal);

        return Inertia::render('crm/deals/Show', $this->dealShowPage->forDeal($request->user(), $deal)->toArray());
    }

    public function edit(Request $request, Deal $deal): Response
    {
        Gate::authorize('update', $deal);

        return Inertia::render('crm/deals/Edit', $this->formData->forDealEdit($request->user(), $deal));
    }

    public function update(UpdateDealRequest $request, Deal $deal, UpdateDeal $updateDeal): RedirectResponse
    {
        Gate::authorize('update', $deal);

        $stage = $this->pipelineRead->findStageOrFail((int) $request->validated('pipeline_stage_id'));
        $updateDeal->handle($deal, $request->toCommand(), $stage);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Deal updated.')]);

        return to_route('deals.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'deal' => $deal->id,
        ]);
    }

    public function move(MoveDealRequest $request, Deal $deal, MoveDeal $moveDeal): RedirectResponse
    {
        Gate::authorize('update', $deal);

        $moveDeal->handle($deal, $request->toCommand());

        return back();
    }

    public function destroy(Request $request, Deal $deal, DeleteDeal $deleteDeal): RedirectResponse
    {
        Gate::authorize('delete', $deal);

        $deleteDeal->handle($deal);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Deal deleted.')]);

        return to_route('deals.board', [
            'current_team' => $request->user()->currentTeam->slug,
        ]);
    }
}
