<?php

namespace App\Application\Tasks\Commands;

final readonly class UpdateTaskCommand
{
    public function __construct(
        public string $title,
        public string $priority,
        public ?string $description = null,
        public ?string $dueAt = null,
        public ?int $assignedTo = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'due_at' => $this->dueAt,
            'priority' => $this->priority,
            'assigned_to' => $this->assignedTo,
        ];
    }
}
