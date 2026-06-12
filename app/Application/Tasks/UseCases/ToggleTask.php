<?php

namespace App\Application\Tasks\UseCases;

use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use App\Models\Task;

class ToggleTask
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
    ) {}

    public function handle(Task $task): Task
    {
        return $this->tasks->toggle($task);
    }
}
