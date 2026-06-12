<?php

namespace App\Domain\Tasks\Repositories;

use App\Models\Task;

interface TaskRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Task;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Task $task, array $data): Task;

    public function toggle(Task $task): Task;

    public function delete(Task $task): void;
}
