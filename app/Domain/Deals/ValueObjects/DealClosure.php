<?php

namespace App\Domain\Deals\ValueObjects;

use App\Domain\Shared\Enums\DealStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final readonly class DealClosure
{
    public function __construct(
        public DealStatus $status,
        public ?CarbonImmutable $closedAt,
    ) {}

    public static function fromTerminalFlags(bool $isWon, bool $isLost, ?CarbonInterface $existingClosedAt = null): self
    {
        $status = match (true) {
            $isWon => DealStatus::Won,
            $isLost => DealStatus::Lost,
            default => DealStatus::Open,
        };

        $closedAt = $status === DealStatus::Open
            ? null
            : ($existingClosedAt !== null ? CarbonImmutable::instance($existingClosedAt) : CarbonImmutable::now());

        return new self($status, $closedAt);
    }

    /**
     * @return array{status: DealStatus, closed_at: CarbonImmutable|null}
     */
    public function toModelAttributes(): array
    {
        return [
            'status' => $this->status,
            'closed_at' => $this->closedAt,
        ];
    }
}
