<?php

namespace App\Application\Activities\Commands;

final readonly class CreateActivityCommand
{
    public function __construct(
        public string $type,
        public string $subjectableType,
        public int $subjectableId,
        public int $userId,
        public ?string $subject = null,
        public ?string $body = null,
        public ?string $occurredAt = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'subject' => $this->subject,
            'body' => $this->body,
            'occurred_at' => $this->occurredAt ?? now(),
            'subjectable_type' => $this->subjectableType,
            'subjectable_id' => $this->subjectableId,
            'user_id' => $this->userId,
        ];
    }
}
