<?php

namespace App\Application\Tasks\UseCases;

use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use App\Models\Task;

class DeleteTask
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
    ) {}

    public function handle(Task $task): void
    {
        $this->tasks->delete($task);
    }
}
