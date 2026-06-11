<?php

namespace App\Http\Controllers\Crm;

use App\Application\Crm\Presenters\CrmRecordShowPresenter;
use App\Application\Crm\Services\CrmFormOptions;
use App\Application\Crm\Services\CustomFields;
use App\Application\Deals\Presenters\DealPresenter;
use App\Application\Deals\UseCases\CreateDeal;
use App\Application\Deals\UseCases\MoveDeal;
use App\Application\Deals\UseCases\UpdateDeal;
use App\Application\Pipelines\Repositories\PipelineReadRepositoryInterface;
use App\Concerns\InteractsWithCrmRecords;
use App\Domain\Pipelines\Repositories\PipelineRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\MoveDealRequest;
use App\Http\Requests\Crm\StoreDealRequest;
use App\Http\Requests\Crm\UpdateDealRequest;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DealController extends Controller
{
    use InteractsWithCrmRecords;

    public function __construct(
        private readonly PipelineRepositoryInterface $pipelines,
        private readonly PipelineReadRepositoryInterface $pipelineRead,
        private readonly DealPresenter $dealPresenter,
        private readonly CrmRecordShowPresenter $showPresenter,
        private readonly CrmFormOptions $formOptions,
        private readonly CustomFields $customFields,
    ) {}

    /**
     * Display the kanban board for a pipeline.
     */
    public function board(Request $request): Response
    {
        Gate::authorize('viewAny', Deal::class);

        $team = $request->user()->currentTeam;
        $this->pipelines->ensureDefaultExists($team->id);

        $pipeline = $this->pipelineRead->resolveForRequest(
            $request->integer('pipeline') ?: null,
        );

        $stages = $pipeline->stages()
            ->with(['deals' => fn ($query) => $query
                ->with(['company:id,name', 'contact:id,first_name,last_name', 'owner:id,name'])
                ->orderBy('position'),
            ])
            ->get()
            ->map(fn (PipelineStage $stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'key' => $stage->key,
                'color' => $stage->color,
                'is_won' => $stage->is_won,
                'is_lost' => $stage->is_lost,
                'total_amount' => (float) $stage->deals->sum(fn (Deal $deal) => (float) $deal->amount),
                'deals' => $stage->deals
                    ->map(fn (Deal $deal) => $this->dealPresenter->card($deal)->toArray())
                    ->values(),
            ]);

        return Inertia::render('crm/deals/Board', [
            'pipeline' => ['id' => $pipeline->id, 'name' => $pipeline->name],
            'pipelines' => Pipeline::query()->orderBy('position')->get(['id', 'name'])
                ->map(fn (Pipeline $p) => ['id' => $p->id, 'name' => $p->name]),
            'stages' => $stages,
            'can' => ['manage' => $request->user()->can('create', Deal::class)],
        ]);
    }

    /**
     * Show the form for creating a deal.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Deal::class);

        $this->pipelines->ensureDefaultExists($request->user()->currentTeam->id);
        $this->pipelineRead->resolveForRequest($request->integer('pipeline') ?: null);

        return Inertia::render('crm/deals/Create', [
            ...$this->formData($request),
            'stageId' => $request->integer('stage_id') ?: null,
            'companyId' => $request->integer('company_id') ?: null,
            'contactId' => $request->integer('contact_id') ?: null,
        ]);
    }

    /**
     * Store a newly created deal at the end of its target stage.
     */
    public function store(StoreDealRequest $request, CreateDeal $createDeal): RedirectResponse
    {
        Gate::authorize('create', Deal::class);

        $stage = $this->pipelineRead->findStageOrFail((int) $request->validated('pipeline_stage_id'));
        $deal = $createDeal->handle($request->validatedWithCustomFields(), $stage);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Deal created.')]);

        return to_route('deals.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'deal' => $deal->id,
        ]);
    }

    /**
     * Display a deal with its timeline and tasks.
     */
    public function show(Request $request, Deal $deal): Response
    {
        Gate::authorize('view', $deal);

        $deal->load(['pipeline:id,name', 'stage:id,name,color,probability', 'company:id,name', 'contact:id,first_name,last_name', 'owner:id,name']);

        $closedOrNow = $deal->closed_at ?? now();
        $sidebar = $this->showPresenter->sidebar($request->user(), $deal, 'deal', $deal);

        return Inertia::render('crm/deals/Show', [
            'deal' => $this->dealPresenter->detail($deal)->toArray(),
            'stats' => [
                'amount' => $deal->amount !== null ? (float) $deal->amount : null,
                'probability' => $deal->stage->probability,
                'days_open' => (int) $deal->created_at?->diffInDays($closedOrNow),
                'tasks_count' => $deal->tasks()->whereNull('completed_at')->count(),
                'activities_count' => $deal->activities()->count(),
            ],
            'stages' => $deal->pipeline->stages()
                ->orderBy('position')
                ->get(['id', 'name', 'color', 'position', 'is_won', 'is_lost'])
                ->map(fn (PipelineStage $stage) => [
                    'id' => $stage->id,
                    'name' => $stage->name,
                    'color' => $stage->color,
                    'is_won' => $stage->is_won,
                    'is_lost' => $stage->is_lost,
                ]),
            ...$sidebar->toArray(),
        ]);
    }

    /**
     * Show the form for editing a deal.
     */
    public function edit(Request $request, Deal $deal): Response
    {
        Gate::authorize('update', $deal);

        return Inertia::render('crm/deals/Edit', [
            ...$this->formData($request),
            'deal' => $this->dealPresenter->detail($deal)->toArray(),
        ]);
    }

    /**
     * Update a deal.
     */
    public function update(UpdateDealRequest $request, Deal $deal, UpdateDeal $updateDeal): RedirectResponse
    {
        Gate::authorize('update', $deal);

        $stage = $this->pipelineRead->findStageOrFail((int) $request->validated('pipeline_stage_id'));
        $updateDeal->handle($deal, $request->validatedWithCustomFields(), $stage);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Deal updated.')]);

        return to_route('deals.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'deal' => $deal->id,
        ]);
    }

    /**
     * Move a deal to a stage/position (kanban drag-and-drop).
     */
    public function move(MoveDealRequest $request, Deal $deal, MoveDeal $moveDeal): RedirectResponse
    {
        Gate::authorize('update', $deal);

        $stage = $this->pipelineRead->findStageOrFail((int) $request->validated('stage_id'));
        $moveDeal->handle($deal, $stage, (int) $request->validated('position'));

        return back();
    }

    /**
     * Delete a deal.
     */
    public function destroy(Request $request, Deal $deal): RedirectResponse
    {
        Gate::authorize('delete', $deal);

        $deal->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Deal deleted.')]);

        return to_route('deals.board', [
            'current_team' => $request->user()->currentTeam->slug,
        ]);
    }

    /**
     * Shared option data for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    protected function formData(Request $request): array
    {
        return [
            'pipelines' => $this->formOptions->pipelinesWithStages(),
            'companies' => array_map(
                fn ($option) => $option->toArray(),
                $this->formOptions->companies(),
            ),
            'contacts' => array_map(
                fn ($option) => $option->toArray(),
                $this->formOptions->contacts(),
            ),
            'owners' => $this->teamMembers($request),
            'customFields' => $this->customFields->forFrontend('deal'),
        ];
    }
}
