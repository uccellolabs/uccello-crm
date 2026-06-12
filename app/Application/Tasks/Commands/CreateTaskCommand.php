<?php

namespace App\Application\Tasks\Commands;

final readonly class CreateTaskCommand
{
    public function __construct(
        public string $title,
        public string $priority,
        public int $createdBy,
        public ?string $description = null,
        public ?string $dueAt = null,
        public ?int $assignedTo = null,
        public ?string $taskableType = null,
        public ?int $taskableId = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'due_at' => $this->dueAt,
            'priority' => $this->priority,
            'assigned_to' => $this->assignedTo,
            'created_by' => $this->createdBy,
        ];

        if ($this->taskableType !== null && $this->taskableId !== null) {
            $data['taskable_type'] = $this->taskableType;
            $data['taskable_id'] = $this->taskableId;
        }

        return $data;
    }
}
