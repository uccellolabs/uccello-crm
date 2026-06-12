<?php

namespace App\Application\Tasks\UseCases;

use App\Application\Tasks\Commands\UpdateTaskCommand;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use App\Models\Task;

class UpdateTask
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
    ) {}

    public function handle(Task $task, UpdateTaskCommand $command): Task
    {
        return $this->tasks->update($task, $command->toArray());
    }
}
