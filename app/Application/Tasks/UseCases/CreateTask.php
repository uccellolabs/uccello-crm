<?php

namespace App\Application\Tasks\UseCases;

use App\Application\Crm\Services\CrmMorphResolver;
use App\Application\Tasks\Commands\CreateTaskCommand;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use App\Models\Task;

class CreateTask
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly CrmMorphResolver $crmMorph,
    ) {}

    public function handle(CreateTaskCommand $command): Task
    {
        $taskableType = $command->taskableType;
        $taskableId = $command->taskableId;

        if ($taskableType !== null && $taskableId !== null) {
            $parent = $this->crmMorph->resolve($taskableType, $taskableId);
            $taskableId = $parent->getKey();
        }

        return $this->tasks->create(new CreateTaskCommand(
            title: $command->title,
            priority: $command->priority,
            createdBy: $command->createdBy,
            description: $command->description,
            dueAt: $command->dueAt,
            assignedTo: $command->assignedTo,
            taskableType: $taskableType,
            taskableId: $taskableId,
        )->toArray());
    }
}
