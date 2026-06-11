<?php

namespace App\Infrastructure\Services;

use App\Application\Crm\Services\Picklists;
use App\Domain\Shared\Enums\Picklist;
use App\Models\PicklistOption;
use Illuminate\Support\Collection;

/**
 * Eloquent-backed {@see Picklists}: lazily seeds the defaults the first time a
 * team touches a list, serves the options for selects, derives allowed values
 * for validation, and resolves stored values back to their human label.
 *
 * Bound as a scoped singleton so the per-request row cache is shared.
 */
class EloquentPicklists implements Picklists
{
    /**
     * Per-request cache of option rows keyed by picklist value.
     *
     * @var array<string, Collection<int, PicklistOption>>
     */
    protected array $cache = [];

    public function rows(Picklist $list): Collection
    {
        if (isset($this->cache[$list->value])) {
            return $this->cache[$list->value];
        }

        $rows = PicklistOption::query()->forList($list->value)->get();

        if ($rows->isEmpty()) {
            foreach ($list->defaults() as $position => $default) {
                PicklistOption::create([...$default, 'picklist' => $list->value, 'position' => $position]);
            }

            $rows = PicklistOption::query()->forList($list->value)->get();
        }

        return $this->cache[$list->value] = $rows;
    }

    public function options(Picklist $list): array
    {
        return array_values($this->rows($list)
            ->map(fn (PicklistOption $option) => [
                'value' => $option->value,
                'label' => $option->label,
                'color' => $option->color,
            ])
            ->all());
    }

    public function values(Picklist $list): array
    {
        return $this->rows($list)->pluck('value')->all();
    }

    public function label(Picklist $list, ?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        foreach ($this->rows($list) as $option) {
            if ($option->value === $value) {
                return $option->label;
            }
        }

        return $value;
    }

    public function flush(): void
    {
        $this->cache = [];
    }
}
