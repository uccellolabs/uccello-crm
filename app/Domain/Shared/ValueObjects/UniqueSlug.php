<?php

namespace App\Domain\Shared\ValueObjects;

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
        $base = self::slugify($label) ?: $fallback;
        $slug = $base;
        $suffix = 1;

        while ($exists($slug)) {
            $slug = $base.'_'.(++$suffix);
        }

        return new self($slug);
    }

    private static function slugify(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = preg_replace('/[^\p{L}\p{N}]+/u', '_', $value) ?? '';
        $value = trim($value, '_');

        return preg_replace('/_+/', '_', $value) ?? '';
    }
}
