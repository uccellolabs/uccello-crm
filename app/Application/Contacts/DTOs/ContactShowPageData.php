<?php

namespace App\Application\Contacts\DTOs;

final readonly class ContactShowPageData
{
    /**
     * @param  array<string, mixed>  $contact
     * @param  array<string, mixed>  $stats
     * @param  list<array<string, mixed>>  $deals
     * @param  list<array<string, mixed>>  $activities
     * @param  list<array<string, mixed>>  $tasks
     * @param  list<array{value: int|string, label: string}>  $members
     * @param  list<array{value: string, label: string}>  $activityTypes
     * @param  list<array{value: string, label: string}>  $taskPriorities
     * @param  list<array<string, mixed>>  $customFields
     * @param  array{update: bool, delete: bool}  $can
     */
    public function __construct(
        public array $contact,
        public array $stats,
        public array $deals,
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
            'contact' => $this->contact,
            'stats' => $this->stats,
            'deals' => $this->deals,
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
