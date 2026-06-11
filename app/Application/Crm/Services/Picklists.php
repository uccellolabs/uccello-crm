<?php

namespace App\Application\Crm\Services;

use App\Domain\Shared\Enums\Picklist;
use App\Models\PicklistOption;
use Illuminate\Support\Collection;

/**
 * Port for team-configurable option lists: serves select options, derives
 * allowed values for validation, and resolves stored values back to their
 * human label. Implemented in the infrastructure layer (Eloquent).
 */
interface Picklists
{
    /**
     * The option rows of a list, seeding the defaults on first use.
     *
     * @return Collection<int, PicklistOption>
     */
    public function rows(Picklist $list): Collection;

    /**
     * Select options for the frontend.
     *
     * @return list<array{value: string, label: string, color: string|null}>
     */
    public function options(Picklist $list): array;

    /**
     * Allowed values for validation rules.
     *
     * @return array<int, string>
     */
    public function values(Picklist $list): array;

    /**
     * The label of a stored value, falling back to the raw value when the
     * option was deleted so historical records keep rendering.
     */
    public function label(Picklist $list, ?string $value): ?string;

    /**
     * Forget the cached rows (after admin writes within the same request).
     */
    public function flush(): void;
}
