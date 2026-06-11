<?php

namespace Tests\Unit\Domain;

use App\Domain\Deals\ValueObjects\DealClosure;
use App\Domain\Shared\Enums\DealStatus;
use App\Domain\Shared\ValueObjects\DateRange;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DealClosureTest extends TestCase
{
    #[Test]
    public function it_derives_open_status_from_non_terminal_stage(): void
    {
        $closure = DealClosure::fromTerminalFlags(isWon: false, isLost: false);

        $this->assertSame(DealStatus::Open, $closure->status);
        $this->assertNull($closure->closedAt);
    }

    #[Test]
    public function it_derives_won_status_with_closed_at(): void
    {
        $existing = CarbonImmutable::parse('2026-01-15');

        $closure = DealClosure::fromTerminalFlags(isWon: true, isLost: false, existingClosedAt: $existing);

        $this->assertSame(DealStatus::Won, $closure->status);
        $this->assertTrue($existing->equalTo($closure->closedAt));
    }

    #[Test]
    public function it_exports_model_attributes(): void
    {
        $attributes = DealClosure::fromTerminalFlags(isWon: false, isLost: true)->toModelAttributes();

        $this->assertSame(DealStatus::Lost, $attributes['status']);
        $this->assertNotNull($attributes['closed_at']);
    }
}

class DateRangeTest extends TestCase
{
    #[Test]
    public function it_calculates_length_in_days(): void
    {
        $range = new DateRange(
            CarbonImmutable::parse('2026-06-01')->startOfDay(),
            CarbonImmutable::parse('2026-06-10')->endOfDay(),
        );

        $this->assertSame(10, $range->lengthInDays());
    }

    #[Test]
    public function it_calculates_previous_period(): void
    {
        $range = new DateRange(
            CarbonImmutable::parse('2026-06-11')->startOfDay(),
            CarbonImmutable::parse('2026-06-20')->endOfDay(),
        );

        $previous = $range->previousPeriod();

        $this->assertSame('2026-06-01', $previous->from->toDateString());
        $this->assertSame('2026-06-10', $previous->to->toDateString());
    }
}
