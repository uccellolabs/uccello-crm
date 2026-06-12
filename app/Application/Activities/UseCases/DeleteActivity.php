<?php

namespace App\Application\Activities\UseCases;

use App\Domain\Activities\Repositories\ActivityRepositoryInterface;
use App\Models\Activity;

class DeleteActivity
{
    public function __construct(
        private readonly ActivityRepositoryInterface $activities,
    ) {}

    public function handle(Activity $activity): void
    {
        $this->activities->delete($activity);
    }
}
