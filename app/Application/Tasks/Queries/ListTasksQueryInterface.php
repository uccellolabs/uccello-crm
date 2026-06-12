<?php

namespace App\Application\Tasks\Queries;

use App\Application\Tasks\DTOs\TasksPageData;
use App\Models\User;

interface ListTasksQueryInterface
{
    public function paginate(User $user, string $status): TasksPageData;
}
