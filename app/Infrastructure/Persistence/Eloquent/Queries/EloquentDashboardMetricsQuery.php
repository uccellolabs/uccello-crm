<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Crm\Services\Picklists;
use App\Application\Dashboard\DTOs\DashboardChartsData;
use App\Application\Dashboard\DTOs\DashboardKpisData;
use App\Application\Dashboard\DTOs\DashboardListsData;
use App\Application\Dashboard\DTOs\DashboardPageData;
use App\Application\Dashboard\Queries\DashboardMetricsQueryInterface;
use App\Domain\Shared\Enums\DealStatus;
use App\Domain\Shared\Enums\Picklist;
use App\Domain\Shared\ValueObjects\DateRange;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\Task;
use Carbon\CarbonInterface;

class EloquentDashboardMetricsQuery implements DashboardMetricsQueryInterface
{
    public function __construct(
        private readonly Picklists $picklists,
    ) {}

    public function forRange(DateRange $range): DashboardPageData
    {
        $previous = $range->previousPeriod();

        return new DashboardPageData(
            range: $range,
            kpis: $this->kpis($range->from, $range->to, $previous->from, $previous->to),
            charts: new DashboardChartsData(
                weekly: $this->weeklySeries($range->from, $range->to),
                byStage: $this->byStage(),
            ),
            lists: new DashboardListsData(
                upcomingTasks: $this->upcomingTasks(),
                recentActivities: $this->recentActivities(),
                topDeals: $this->topDeals(),
            ),
        );
    }

    private function kpis(
        CarbonInterface $from,
        CarbonInterface $to,
        CarbonInterface $prevFrom,
        CarbonInterface $prevTo,
    ): DashboardKpisData {
        $newCompanies = Company::whereBetween('created_at', [$from, $to])->count();
        $newContacts = Contact::whereBetween('created_at', [$from, $to])->count();
        $dealsCreated = Deal::whereBetween('created_at', [$from, $to])->count();
        $activities = Activity::whereBetween('occurred_at', [$from, $to])->count();

        $wonDeals = Deal::where('status', DealStatus::Won)->whereBetween('closed_at', [$from, $to]);
        $wonCount = (clone $wonDeals)->count();
        $wonAmount = (float) (clone $wonDeals)->sum('amount');

        $closedInRange = Deal::whereIn('status', [DealStatus::Won, DealStatus::Lost])
            ->whereBetween('closed_at', [$from, $to])->count();
        $conversion = $closedInRange > 0 ? (int) round($wonCount / $closedInRange * 100) : 0;

        return new DashboardKpisData(
            newCompanies: [
                'value' => $newCompanies,
                'previous' => Company::whereBetween('created_at', [$prevFrom, $prevTo])->count(),
            ],
            newContacts: [
                'value' => $newContacts,
                'previous' => Contact::whereBetween('created_at', [$prevFrom, $prevTo])->count(),
            ],
            dealsCreated: [
                'value' => $dealsCreated,
                'previous' => Deal::whereBetween('created_at', [$prevFrom, $prevTo])->count(),
            ],
            dealsWon: [
                'value' => $wonCount,
                'amount' => $wonAmount,
                'previous' => Deal::where('status', DealStatus::Won)
                    ->whereBetween('closed_at', [$prevFrom, $prevTo])->count(),
            ],
            activities: [
                'value' => $activities,
                'previous' => Activity::whereBetween('occurred_at', [$prevFrom, $prevTo])->count(),
            ],
            pipelineValue: ['value' => (float) Deal::where('status', DealStatus::Open)->sum('amount')],
            openDeals: ['value' => Deal::where('status', DealStatus::Open)->count()],
            conversionRate: ['value' => $conversion],
            overdueTasks: [
                'value' => Task::whereNull('completed_at')
                    ->whereNotNull('due_at')
                    ->where('due_at', '<', now())
                    ->count(),
            ],
        );
    }

    /** @return list<array<string, mixed>> */
    private function weeklySeries(CarbonInterface $from, CarbonInterface $to): array
    {
        $created = Deal::query()
            ->selectRaw("date_trunc('week', created_at) as week, count(*) as total")
            ->whereBetween('created_at', [$from, $to])
            ->groupByRaw("date_trunc('week', created_at)")
            ->pluck('total', 'week');

        $won = Deal::query()
            ->selectRaw("date_trunc('week', closed_at) as week, count(*) as total")
            ->where('status', DealStatus::Won)
            ->whereBetween('closed_at', [$from, $to])
            ->groupByRaw("date_trunc('week', closed_at)")
            ->pluck('total', 'week');

        $series = [];
        $cursor = $from->copy()->startOfWeek();

        while ($cursor->lessThanOrEqualTo($to)) {
            $key = $cursor->toDateTimeString();
            $series[] = [
                'week' => $cursor->toDateString(),
                'created' => (int) ($created[$key] ?? 0),
                'won' => (int) ($won[$key] ?? 0),
            ];
            $cursor = $cursor->addWeek();
        }

        return $series;
    }

    /** @return list<array<string, mixed>> */
    private function byStage(): array
    {
        return array_values(PipelineStage::query()
            ->where('is_won', false)
            ->where('is_lost', false)
            ->withCount(['deals as open_count' => fn ($q) => $q->where('status', DealStatus::Open)])
            ->withSum(['deals as open_amount' => fn ($q) => $q->where('status', DealStatus::Open)], 'amount')
            ->orderBy('position')
            ->get()
            ->map(fn (PipelineStage $stage) => [
                'name' => $stage->name,
                'color' => $stage->color,
                'count' => (int) $stage->getAttribute('open_count'),
                'amount' => (float) ($stage->getAttribute('open_amount') ?? 0),
            ])
            ->all());
    }

    /** @return list<array<string, mixed>> */
    private function upcomingTasks(): array
    {
        return array_values(Task::query()
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->orderBy('due_at')
            ->limit(5)
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'due_at' => $task->due_at?->toISOString(),
                'is_overdue' => $task->due_at !== null && $task->due_at->isPast(),
            ])
            ->all());
    }

    /** @return list<array<string, mixed>> */
    private function recentActivities(): array
    {
        return array_values(Activity::query()
            ->with('user:id,name')
            ->latest('occurred_at')
            ->limit(5)
            ->get()
            ->map(fn (Activity $activity) => [
                'id' => $activity->id,
                'type' => $activity->type,
                'type_label' => $this->picklists->label(Picklist::ActivityType, $activity->type),
                'subject' => $activity->subject,
                'occurred_at' => $activity->occurred_at->toISOString(),
                'user' => $activity->user?->name,
            ])
            ->all());
    }

    /** @return list<array<string, mixed>> */
    private function topDeals(): array
    {
        return array_values(Deal::query()
            ->where('status', DealStatus::Open)
            ->with(['company:id,name', 'stage:id,name'])
            ->orderByDesc('amount')
            ->limit(5)
            ->get()
            ->map(fn (Deal $deal) => [
                'id' => $deal->id,
                'name' => $deal->name,
                'amount' => $deal->amount !== null ? (float) $deal->amount : null,
                'currency' => $deal->currency,
                'company' => $deal->company?->name,
                'stage' => $deal->stage->name,
            ])
            ->all());
    }
}
