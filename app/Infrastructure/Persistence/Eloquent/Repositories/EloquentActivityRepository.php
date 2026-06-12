<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Activities\Repositories\ActivityRepositoryInterface;
use App\Models\Activity;

class EloquentActivityRepository implements ActivityRepositoryInterface
{
    public function create(array $data): Activity
    {
        return Activity::create($data);
    }

    public function delete(Activity $activity): void
    {
        $activity->delete();
    }
}
