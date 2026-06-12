<?php

namespace App\Application\Crm\Presenters;

use App\Application\Crm\DTOs\CrmRecordSidebarData;
use App\Application\Crm\DTOs\SelectOptionData;
use App\Application\Crm\Repositories\CrmTimelineReadRepositoryInterface;
use App\Application\Crm\Services\CrmFormOptions;
use App\Application\Crm\Services\CustomFields;
use App\Application\Crm\Services\Picklists;
use App\Application\Deals\DTOs\DealSummaryData;
use App\Application\Deals\Presenters\DealPresenter;
use App\Application\Deals\Repositories\DealReadRepositoryInterface;
use App\Contracts\HasCrmTimeline;
use App\Domain\Shared\Enums\Picklist;
use App\Models\Activity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CrmRecordShowPresenter
{
    public function __construct(
        private readonly DealReadRepositoryInterface $deals,
        private readonly DealPresenter $dealPresenter,
        private readonly CrmFormOptions $formOptions,
        private readonly CustomFields $customFields,
        private readonly Picklists $picklists,
        private readonly CrmTimelineReadRepositoryInterface $timeline,
    ) {}

    public function sidebar(User $user, HasCrmTimeline $record, string $entityType): CrmRecordSidebarData
    {
        return new CrmRecordSidebarData(
            activities: $this->activities($record),
            tasks: $this->tasks($record),
            members: $this->formOptions->owners($user),
            activityTypes: $this->picklists->options(Picklist::ActivityType),
            taskPriorities: $this->picklists->options(Picklist::TaskPriority),
            customFields: $this->customFields->forFrontend($entityType),
        );
    }

    /** @return list<DealSummaryData> */
    public function dealsForRecord(Model $record): array
    {
        return array_values($this->deals->summariesForRecord($record)
            ->map(fn ($deal) => $this->dealPresenter->summary($deal))
            ->all());
    }

    /** @return list<array{value: int|string, label: string}> */
    public function membersAsArray(User $user): array
    {
        return array_map(
            fn (SelectOptionData $option) => $option->toArray(),
            $this->formOptions->owners($user),
        );
    }

    /** @return list<array<string, mixed>> */
    private function activities(HasCrmTimeline $record): array
    {
        return array_values($this->timeline->activitiesFor($record)
            ->map(fn (Activity $activity) => [
                'id' => $activity->id,
                'type' => $activity->type,
                'type_label' => $this->picklists->label(Picklist::ActivityType, $activity->type),
                'subject' => $activity->subject,
                'body' => $activity->body,
                'occurred_at' => $activity->occurred_at->toISOString(),
                'user' => $activity->user ? ['id' => $activity->user->id, 'name' => $activity->user->name] : null,
            ])
            ->all());
    }

    /** @return list<array<string, mixed>> */
    private function tasks(HasCrmTimeline $record): array
    {
        return array_values($this->timeline->tasksFor($record)
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'due_at' => $task->due_at?->toISOString(),
                'priority' => $task->priority,
                'priority_label' => $this->picklists->label(Picklist::TaskPriority, $task->priority),
                'is_completed' => $task->isCompleted(),
                'completed_at' => $task->completed_at?->toISOString(),
                'assignee' => $task->assignee ? ['id' => $task->assignee->id, 'name' => $task->assignee->name] : null,
            ])
            ->all());
    }
}
