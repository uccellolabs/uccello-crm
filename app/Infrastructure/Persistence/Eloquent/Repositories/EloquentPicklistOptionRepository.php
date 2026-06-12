<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Picklists\Repositories\PicklistOptionRepositoryInterface;
use App\Domain\Shared\Enums\Picklist;
use App\Domain\Shared\Exceptions\InvalidReorderException;
use App\Models\PicklistOption;
use Illuminate\Database\Eloquent\Model;

class EloquentPicklistOptionRepository implements PicklistOptionRepositoryInterface
{
    public function create(array $data): PicklistOption
    {
        return PicklistOption::create($data);
    }

    public function update(PicklistOption $option, array $data): PicklistOption
    {
        $option->update($data);

        return $option->fresh();
    }

    public function delete(PicklistOption $option): void
    {
        $option->delete();
    }

    public function nextPosition(Picklist $picklist): int
    {
        return (int) PicklistOption::query()->forList($picklist->value)->max('position') + 1;
    }

    public function reorder(array $ids): void
    {
        $options = PicklistOption::query()->whereIn('id', $ids)->get();

        if ($options->count() !== count($ids)) {
            throw InvalidReorderException::idCountMismatch();
        }

        $options->each(fn (Model $option) => $option->update([
            'position' => array_search($option->getKey(), $ids, true),
        ]));
    }

    public function valueExists(Picklist $picklist, string $value): bool
    {
        return PicklistOption::query()
            ->where('picklist', $picklist->value)
            ->where('value', $value)
            ->exists();
    }
}
