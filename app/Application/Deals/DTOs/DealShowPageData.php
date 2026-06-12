<?php

namespace App\Application\Deals\DTOs;

final readonly class DealShowPageData
{
    /**
     * @param  array<string, mixed>  $deal
     * @param  array<string, mixed>  $stats
     * @param  list<array<string, mixed>>  $stages
     * @param  list<array<string, mixed>>  $activities
     * @param  list<array<string, mixed>>  $tasks
     * @param  list<array{value: int|string, label: string}>  $members
     * @param  list<array{value: string, label: string}>  $activityTypes
     * @param  list<array{value: string, label: string}>  $taskPriorities
     * @param  list<array<string, mixed>>  $customFields
     * @param  array{update: bool, delete: bool}  $can
     */
    public function __construct(
        public array $deal,
        public array $stats,
        public array $stages,
        public array $activities,
        public array $tasks,
        public array $members,
        public array $activityTypes,
        public array $taskPriorities,
        public array $customFields,
        public array $can,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'deal' => $this->deal,
            'stats' => $this->stats,
            'stages' => $this->stages,
            'activities' => $this->activities,
            'tasks' => $this->tasks,
            'members' => $this->members,
            'activityTypes' => $this->activityTypes,
            'taskPriorities' => $this->taskPriorities,
            'customFields' => $this->customFields,
            'can' => $this->can,
        ];
    }
}
