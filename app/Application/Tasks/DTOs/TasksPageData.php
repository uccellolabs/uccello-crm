<?php

namespace App\Application\Tasks\DTOs;

final readonly class TasksPageData
{
    /**
     * @param  array{status: string}  $filters
     * @param  list<array{value: int|string, label: string}>  $assignees
     * @param  list<array{value: string, label: string}>  $taskPriorities
     * @param  array{create: bool}  $can
     */
    public function __construct(
        public mixed $tasks,
        public array $filters,
        public array $assignees,
        public array $taskPriorities,
        public array $can,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'tasks' => $this->tasks,
            'filters' => $this->filters,
            'assignees' => $this->assignees,
            'taskPriorities' => $this->taskPriorities,
            'can' => $this->can,
        ];
    }
}
