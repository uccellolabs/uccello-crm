<?php

namespace App\Infrastructure\Persistence\Eloquent\Queries;

use App\Application\Crm\Services\CrmFormOptions;
use App\Application\Crm\Services\Picklists;
use App\Application\Shared\Ports\AuthorizationCheckerInterface;
use App\Application\Tasks\DTOs\TasksPageData;
use App\Application\Tasks\Presenters\TaskPresenter;
use App\Application\Tasks\Queries\ListTasksQueryInterface;
use App\Domain\Shared\Enums\Picklist;
use App\Models\Task;
use App\Models\User;

class EloquentListTasksQuery implements ListTasksQueryInterface
{
    public function __construct(
        private readonly TaskPresenter $presenter,
        private readonly CrmFormOptions $formOptions,
        private readonly Picklists $picklists,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {}

    public function paginate(User $user, string $status): TasksPageData
    {
        $tasks = Task::query()
            ->with(['assignee:id,name', 'taskable'])
            ->when($status === 'open', fn ($query) => $query->whereNull('completed_at'))
            ->when($status === 'completed', fn ($query) => $query->whereNotNull('completed_at'))
            ->orderByRaw('completed_at is null desc')
            ->orderByRaw('due_at asc nulls last')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Task $task) => $this->presenter->toListItem($task));

        return new TasksPageData(
            tasks: $tasks,
            filters: ['status' => $status],
            assignees: array_map(
                fn ($option) => $option->toArray(),
                $this->formOptions->owners($user),
            ),
            taskPriorities: $this->picklists->options(Picklist::TaskPriority),
            can: ['create' => $this->authorization->can($user, 'create', Task::class)],
        );
    }
}
