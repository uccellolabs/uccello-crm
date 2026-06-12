<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Crm\Repositories\CrmTimelineReadRepositoryInterface;
use App\Contracts\HasCrmTimeline;
use App\Models\Activity;
use App\Models\Task;
use Illuminate\Support\Collection;

class EloquentCrmTimelineReadRepository implements CrmTimelineReadRepositoryInterface
{
    public function activitiesFor(HasCrmTimeline $record): Collection
    {
        return $record->activities()
            ->with('user:id,name')
            ->latest('occurred_at')
            ->get();
    }

    public function tasksFor(HasCrmTimeline $record): Collection
    {
        return $record->tasks()
            ->with('assignee:id,name')
            ->orderByRaw('completed_at is null desc')
            ->orderByRaw('due_at asc nulls last')
            ->get();
    }
}
