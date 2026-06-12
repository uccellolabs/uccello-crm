<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use App\Models\Task;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    public function toggle(Task $task): Task
    {
        $task->update([
            'completed_at' => $task->completed_at ? null : now(),
        ]);

        return $task->fresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
