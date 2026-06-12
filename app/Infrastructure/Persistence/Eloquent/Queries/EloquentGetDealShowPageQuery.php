<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Crm\Presenters\CrmRecordShowPresenter;
use App\Application\Deals\DTOs\DealShowPageData;
use App\Application\Deals\Presenters\DealPresenter;
use App\Application\Deals\Queries\GetDealShowPageQueryInterface;
use App\Application\Shared\Ports\AuthorizationCheckerInterface;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\User;

class EloquentGetDealShowPageQuery implements GetDealShowPageQueryInterface
{
    public function __construct(
        private readonly DealPresenter $dealPresenter,
        private readonly CrmRecordShowPresenter $showPresenter,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {}

    public function forDeal(User $user, Deal $deal): DealShowPageData
    {
        $deal->load(['pipeline:id,name', 'stage:id,name,color,probability', 'company:id,name', 'contact:id,first_name,last_name', 'owner:id,name']);

        $closedOrNow = $deal->closed_at ?? now();

        $stats = [
            'amount' => $deal->amount !== null ? (float) $deal->amount : null,
            'probability' => $deal->stage->probability,
            'days_open' => (int) $deal->created_at?->diffInDays($closedOrNow),
            'tasks_count' => $deal->tasks()->whereNull('completed_at')->count(),
            'activities_count' => $deal->activities()->count(),
        ];

        $stages = $deal->pipeline->stages()
            ->orderBy('position')
            ->get(['id', 'name', 'color', 'position', 'is_won', 'is_lost'])
            ->map(fn (PipelineStage $stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'is_won' => $stage->is_won,
                'is_lost' => $stage->is_lost,
            ])
            ->values()
            ->all();

        $sidebar = $this->showPresenter->sidebar($user, $deal, 'deal');

        return new DealShowPageData(
            deal: $this->dealPresenter->detail($deal)->toArray(),
            stats: $stats,
            stages: array_values($stages),
            activities: $sidebar->activities,
            tasks: $sidebar->tasks,
            members: array_map(fn ($member) => $member->toArray(), $sidebar->members),
            activityTypes: $sidebar->activityTypes,
            taskPriorities: $sidebar->taskPriorities,
            customFields: $sidebar->customFields,
            can: [
                'update' => $this->authorization->can($user, 'update', $deal),
                'delete' => $this->authorization->can($user, 'delete', $deal),
            ],
        );
    }
}
