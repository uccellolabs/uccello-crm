<?php

namespace Tests\Unit\Application\Shared;

use App\Application\Shared\Results\DeletionResult;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeletionResultTest extends TestCase
{
    #[Test]
    public function success_is_reported_as_succeeded(): void
    {
        $this->assertTrue(DeletionResult::Success->succeeded());
    }

    #[Test]
    public function blocked_results_are_not_succeeded(): void
    {
        $this->assertFalse(DeletionResult::BlockedTerminalStage->succeeded());
        $this->assertFalse(DeletionResult::BlockedHasDeals->succeeded());
    }
}
