<?php

namespace App\Http\Controllers\Crm;

use App\Application\Crm\Queries\GetCrmRecordFormDataQueryInterface;
use App\Application\Tasks\Queries\ListTasksQueryInterface;
use App\Application\Tasks\UseCases\CreateTask;
use App\Application\Tasks\UseCases\DeleteTask;
use App\Application\Tasks\UseCases\ToggleTask;
use App\Application\Tasks\UseCases\UpdateTask;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreTaskRequest;
use App\Http\Requests\Crm\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly ListTasksQueryInterface $listTasks,
        private readonly GetCrmRecordFormDataQueryInterface $formData,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Task::class);

        $filter = $request->string('status', 'open')->toString();

        return Inertia::render('crm/tasks/Index', $this->listTasks->paginate($request->user(), $filter)->toArray());
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Task::class);

        return Inertia::render('crm/tasks/Create', $this->formData->forTaskCreate($request->user()));
    }

    public function store(StoreTaskRequest $request, CreateTask $createTask): RedirectResponse
    {
        Gate::authorize('create', Task::class);

        $createTask->handle($request->toCommand($request->user()->id));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task created.')]);

        if ($request->boolean('to_index')) {
            return to_route('tasks.index', [
                'current_team' => $request->user()->currentTeam->slug,
            ]);
        }

        return back();
    }

    public function update(UpdateTaskRequest $request, Task $task, UpdateTask $updateTask): RedirectResponse
    {
        Gate::authorize('update', $task);

        $updateTask->handle($task, $request->toCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task updated.')]);

        return back();
    }

    public function toggle(Request $request, Task $task, ToggleTask $toggleTask): RedirectResponse
    {
        Gate::authorize('update', $task);

        $toggleTask->handle($task);

        return back();
    }

    public function destroy(Task $task, DeleteTask $deleteTask): RedirectResponse
    {
        Gate::authorize('delete', $task);

        $deleteTask->handle($task);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task deleted.')]);

        return back();
    }
}
