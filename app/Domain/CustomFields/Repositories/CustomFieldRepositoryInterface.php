<?php

namespace App\Domain\CustomFields\Repositories;

use App\Models\CustomFieldDefinition;

interface CustomFieldRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CustomFieldDefinition;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CustomFieldDefinition $definition, array $data): CustomFieldDefinition;

    public function delete(CustomFieldDefinition $definition): void;

    public function nextPosition(string $entityType): int;

    /**
     * @param  list<int>  $ids
     */
    public function reorder(array $ids): void;

    public function keyExists(string $entityType, string $key): bool;
}
