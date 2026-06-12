<?php

namespace App\Application\CustomFields\DTOs;

final readonly class CustomFieldsAdminPageData
{
    /**
     * @param  array<string, list<array<string, mixed>>>  $definitions
     * @param  list<array{value: string, label: string}>  $entities
     * @param  list<array{value: string, label: string}>  $fieldTypes
     */
    public function __construct(
        public array $definitions,
        public array $entities,
        public array $fieldTypes,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'definitions' => $this->definitions,
            'entities' => $this->entities,
            'fieldTypes' => $this->fieldTypes,
        ];
    }
}
