<?php

namespace App\Application\Crm\Repositories;

use App\Contracts\HasCrmTimeline;
use App\Models\Activity;
use App\Models\Task;
use Illuminate\Support\Collection;

interface CrmTimelineReadRepositoryInterface
{
    /** @return Collection<int, Activity> */
    public function activitiesFor(HasCrmTimeline $record): Collection;

    /** @return Collection<int, Task> */
    public function tasksFor(HasCrmTimeline $record): Collection;
}
