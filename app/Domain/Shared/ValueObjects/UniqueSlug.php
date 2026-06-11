<?php

namespace App\Domain\Shared\ValueObjects;

use Illuminate\Support\Str;

final readonly class UniqueSlug
{
    public function __construct(
        public string $value,
    ) {}

    /**
     * @param  callable(string): bool  $exists
     */
    public static function generate(string $label, callable $exists, string $fallback = 'item'): self
    {
        $base = Str::slug($label, '_') ?: $fallback;
        $slug = $base;
        $suffix = 1;

        while ($exists($slug)) {
            $slug = $base.'_'.(++$suffix);
        }

        return new self($slug);
    }
}
