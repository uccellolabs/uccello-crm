<?php

namespace App\Http\Controllers\Crm;

use App\Application\Crm\Services\CrmFormOptions;
use App\Application\Crm\Services\Picklists;
use App\Concerns\InteractsWithCrmRecords;
use App\Domain\Shared\Enums\Picklist;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreTaskRequest;
use App\Http\Requests\Crm\UpdateTaskRequest;
use App\Infrastructure\Services\CrmMorph;
use App\Models\Deal;
use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    use InteractsWithCrmRecords;

    public function __construct(
        private readonly CrmFormOptions $formOptions,
        private readonly Picklists $picklists,
    ) {}

    /**
     * Display the team's tasks.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Task::class);

        $filter = $request->string('status', 'open')->toString();

        $tasks = Task::query()
            ->with(['assignee:id,name', 'taskable'])
            ->when($filter === 'open', fn ($query) => $query->whereNull('completed_at'))
            ->when($filter === 'completed', fn ($query) => $query->whereNotNull('completed_at'))
            ->orderByRaw('completed_at is null desc')
            ->orderByRaw('due_at asc nulls last')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Task $task) => $this->toListItem($task));

        return Inertia::render('crm/tasks/Index', [
            'tasks' => $tasks,
            'filters' => ['status' => $filter],
            'assignees' => $this->teamMembers($request),
            'taskPriorities' => $this->picklists->options(Picklist::TaskPriority),
            'can' => ['create' => $request->user()->can('create', Task::class)],
        ]);
    }

    /**
     * Show the form for creating a task.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Task::class);

        return Inertia::render('crm/tasks/Create', [
            'assignees' => $this->teamMembers($request),
            'taskPriorities' => $this->picklists->options(Picklist::TaskPriority),
            'relatable' => [
                'company' => array_map(fn ($o) => $o->toArray(), $this->formOptions->companies()),
                'contact' => array_map(fn ($o) => $o->toArray(), $this->formOptions->contacts()),
                'deal' => Deal::query()
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (Deal $deal) => ['value' => $deal->id, 'label' => $deal->name]),
            ],
        ]);
    }

    /**
     * Store a newly created task (optionally attached to a CRM record).
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        Gate::authorize('create', Task::class);

        $validated = $request->validated();

        $attributes = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'priority' => $validated['priority'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $request->user()->id,
        ];

        if (! empty($validated['taskable_type']) && ! empty($validated['taskable_id'])) {
            $parent = CrmMorph::resolve($validated['taskable_type'], (int) $validated['taskable_id']);
            $attributes['taskable_type'] = $validated['taskable_type'];
            $attributes['taskable_id'] = $parent->getKey();
        }

        Task::create($attributes);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task created.')]);

        if ($request->boolean('to_index')) {
            return to_route('tasks.index', [
                'current_team' => $request->user()->currentTeam->slug,
            ]);
        }

        return back();
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $task->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task updated.')]);

        return back();
    }

    /**
     * Toggle the completion state of a task.
     */
    public function toggle(Request $request, Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $task->update([
            'completed_at' => $task->completed_at ? null : now(),
        ]);

        return back();
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): RedirectResponse
    {
        Gate::authorize('delete', $task);

        $task->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task deleted.')]);

        return back();
    }

    /**
     * Transform a task into a list payload.
     *
     * @return array<string, mixed>
     */
    protected function toListItem(Task $task): array
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
     * A human label for the task's polymorphic parent.
     *
     * @return array<string, mixed>|null
     */
    protected function relatedLabel(?Model $taskable): ?array
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
