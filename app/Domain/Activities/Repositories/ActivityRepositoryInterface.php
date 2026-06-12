<?php

namespace App\Domain\Activities\Repositories;

use App\Models\Activity;

interface ActivityRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Activity;

    public function delete(Activity $activity): void;
}
