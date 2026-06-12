<?php

namespace App\Application\Tasks\Presenters;

use App\Application\Crm\Services\Picklists;
use App\Domain\Shared\Enums\Picklist;
use App\Models\Task;
use Illuminate\Database\Eloquent\Model;

class TaskPresenter
{
    public function __construct(
        private readonly Picklists $picklists,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toListItem(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'due_at' => $task->due_at?->toISOString(),
            'priority' => $task->priority,
            'priority_label' => $this->picklists->label(Picklist::TaskPriority, $task->priority),
            'is_completed' => $task->isCompleted(),
            'completed_at' => $task->completed_at?->toISOString(),
            'assignee' => $task->assignee ? ['id' => $task->assignee->id, 'name' => $task->assignee->name] : null,
            'related' => $this->relatedLabel($task->taskable),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function relatedLabel(?Model $taskable): ?array
    {
        if ($taskable === null) {
            return null;
        }

        return [
            'type' => $taskable->getMorphClass(),
            'id' => $taskable->getKey(),
            'label' => $taskable->name ?? $taskable->full_name ?? '#'.$taskable->getKey(),
        ];
    }
}
