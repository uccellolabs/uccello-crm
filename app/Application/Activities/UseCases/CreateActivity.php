<?php

namespace App\Application\Activities\UseCases;

use App\Application\Activities\Commands\CreateActivityCommand;
use App\Application\Crm\Services\CrmMorphResolver;
use App\Domain\Activities\Repositories\ActivityRepositoryInterface;
use App\Models\Activity;

class CreateActivity
{
    public function __construct(
        private readonly ActivityRepositoryInterface $activities,
        private readonly CrmMorphResolver $crmMorph,
    ) {}

    public function handle(CreateActivityCommand $command): Activity
    {
        $subject = $this->crmMorph->resolve($command->subjectableType, $command->subjectableId);

        return $this->activities->create(new CreateActivityCommand(
            type: $command->type,
            subjectableType: $command->subjectableType,
            subjectableId: $subject->getKey(),
            userId: $command->userId,
            subject: $command->subject,
            body: $command->body,
            occurredAt: $command->occurredAt,
        )->toArray());
    }
}
