<?php

namespace App\Domain\Shared\ValueObjects;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final readonly class DateRange
{
    public function __construct(
        public CarbonInterface $from,
        public CarbonInterface $to,
    ) {
        if ($from->greaterThan($to)) {
            throw new \InvalidArgumentException('Date range from must be before or equal to to.');
        }
    }

    /**
     * Build a range from raw `from`/`to` date strings (empty = unset), falling
     * back to the last `$defaultDays` days. Kept HTTP-free: the caller extracts
     * the strings from the request.
     */
    public static function fromStrings(string $from = '', string $to = '', int $defaultDays = 30): self
    {
        $to = self::parseDate($to)?->endOfDay() ?? now()->endOfDay();
        $from = self::parseDate($from)?->startOfDay()
            ?? $to->copy()->subDays($defaultDays - 1)->startOfDay();

        if ($from->greaterThan($to)) {
            return new self($to->copy()->startOfDay(), $from->copy()->endOfDay());
        }

        return new self($from, $to);
    }

    public function lengthInDays(): int
    {
        return (int) $this->from->copy()->startOfDay()->diffInDays($this->to->copy()->startOfDay()) + 1;
    }

    public function previousPeriod(): self
    {
        $length = $this->lengthInDays();
        $prevTo = $this->from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($length - 1)->startOfDay();

        return new self($prevFrom, $prevTo);
    }

    /**
     * @return array{from: string, to: string}
     */
    public function toDateStrings(): array
    {
        return [
            'from' => $this->from->toDateString(),
            'to' => $this->to->toDateString(),
        ];
    }

    private static function parseDate(string $value): ?CarbonInterface
    {
        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Exception) {
            return null;
        }
    }
}
