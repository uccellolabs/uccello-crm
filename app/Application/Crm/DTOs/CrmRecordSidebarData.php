<?php

namespace App\Application\Crm\DTOs;

final readonly class CrmRecordSidebarData
{
    /**
     * @param  list<array<string, mixed>>  $activities
     * @param  list<array<string, mixed>>  $tasks
     * @param  list<SelectOptionData>  $members
     * @param  list<array{value: string, label: string}>  $activityTypes
     * @param  list<array{value: string, label: string}>  $taskPriorities
     * @param  list<array<string, mixed>>  $customFields
     */
    public function __construct(
        public array $activities,
        public array $tasks,
        public array $members,
        public array $activityTypes,
        public array $taskPriorities,
        public array $customFields,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'activities' => $this->activities,
            'tasks' => $this->tasks,
            'members' => array_map(fn (SelectOptionData $member) => $member->toArray(), $this->members),
            'activityTypes' => $this->activityTypes,
            'taskPriorities' => $this->taskPriorities,
            'customFields' => $this->customFields,
        ];
    }
}
