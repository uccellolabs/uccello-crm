<?php

namespace App\Application\Picklists\DTOs;

final readonly class PicklistAdminPageData
{
    /**
     * @param  array<string, list<array<string, mixed>>>  $options
     * @param  list<array{value: string, label: string, description: string}>  $picklists
     */
    public function __construct(
        public array $options,
        public array $picklists,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'options' => $this->options,
            'picklists' => $this->picklists,
        ];
    }
}
