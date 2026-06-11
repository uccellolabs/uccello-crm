<?php

namespace App\Application\Dashboard\DTOs;

final readonly class DashboardListsData
{
    /**
     * @param  list<array<string, mixed>>  $upcomingTasks
     * @param  list<array<string, mixed>>  $recentActivities
     * @param  list<array<string, mixed>>  $topDeals
     */
    public function __construct(
        public array $upcomingTasks,
        public array $recentActivities,
        public array $topDeals,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'upcoming_tasks' => $this->upcomingTasks,
            'recent_activities' => $this->recentActivities,
            'top_deals' => $this->topDeals,
        ];
    }
}
