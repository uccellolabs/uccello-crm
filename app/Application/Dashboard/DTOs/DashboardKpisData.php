<?php

namespace App\Application\Dashboard\DTOs;

final readonly class DashboardKpisData
{
    /**
     * @param  array{value: int, previous: int}  $newCompanies
     * @param  array{value: int, previous: int}  $newContacts
     * @param  array{value: int, previous: int}  $dealsCreated
     * @param  array{value: int, amount: float, previous: int}  $dealsWon
     * @param  array{value: int, previous: int}  $activities
     * @param  array{value: float}  $pipelineValue
     * @param  array{value: int}  $openDeals
     * @param  array{value: int}  $conversionRate
     * @param  array{value: int}  $overdueTasks
     */
    public function __construct(
        public array $newCompanies,
        public array $newContacts,
        public array $dealsCreated,
        public array $dealsWon,
        public array $activities,
        public array $pipelineValue,
        public array $openDeals,
        public array $conversionRate,
        public array $overdueTasks,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'new_companies' => $this->newCompanies,
            'new_contacts' => $this->newContacts,
            'deals_created' => $this->dealsCreated,
            'deals_won' => $this->dealsWon,
            'activities' => $this->activities,
            'pipeline_value' => $this->pipelineValue,
            'open_deals' => $this->openDeals,
            'conversion_rate' => $this->conversionRate,
            'overdue_tasks' => $this->overdueTasks,
        ];
    }
}
