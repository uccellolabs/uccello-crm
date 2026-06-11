<?php

namespace App\Contracts;

use App\Models\Activity;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A CRM record that can carry polymorphic tasks and a logged activity
 * timeline (companies, contacts, deals).
 */
interface HasCrmTimeline
{
    /**
     * @return MorphMany<Task, covariant \Illuminate\Database\Eloquent\Model>
     */
    public function tasks(): MorphMany;

    /**
     * @return MorphMany<Activity, covariant \Illuminate\Database\Eloquent\Model>
     */
    public function activities(): MorphMany;
}
