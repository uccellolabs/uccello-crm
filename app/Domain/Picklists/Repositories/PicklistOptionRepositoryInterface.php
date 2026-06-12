<?php

namespace App\Domain\Picklists\Repositories;

use App\Domain\Shared\Enums\Picklist;
use App\Models\PicklistOption;

interface PicklistOptionRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PicklistOption;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PicklistOption $option, array $data): PicklistOption;

    public function delete(PicklistOption $option): void;

    public function nextPosition(Picklist $picklist): int;

    /**
     * @param  list<int>  $ids
     */
    public function reorder(array $ids): void;

    public function valueExists(Picklist $picklist, string $value): bool;
}
