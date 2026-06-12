<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\CustomFields\Repositories\CustomFieldRepositoryInterface;
use App\Domain\Shared\Exceptions\InvalidReorderException;
use App\Models\CustomFieldDefinition;
use Illuminate\Database\Eloquent\Model;

class EloquentCustomFieldRepository implements CustomFieldRepositoryInterface
{
    public function create(array $data): CustomFieldDefinition
    {
        return CustomFieldDefinition::create($data);
    }

    public function update(CustomFieldDefinition $definition, array $data): CustomFieldDefinition
    {
        $definition->update($data);

        return $definition->fresh();
    }

    public function delete(CustomFieldDefinition $definition): void
    {
        $definition->delete();
    }

    public function nextPosition(string $entityType): int
    {
        return (int) CustomFieldDefinition::query()
            ->forEntity($entityType)
            ->max('position') + 1;
    }

    public function reorder(array $ids): void
    {
        $definitions = CustomFieldDefinition::query()->whereIn('id', $ids)->get();

        if ($definitions->count() !== count($ids)) {
            throw InvalidReorderException::idCountMismatch();
        }

        $definitions->each(fn (Model $definition) => $definition->update([
            'position' => array_search($definition->getKey(), $ids, true),
        ]));
    }

    public function keyExists(string $entityType, string $key): bool
    {
        return CustomFieldDefinition::query()
            ->where('entity_type', $entityType)
            ->where('key', $key)
            ->exists();
    }
}
