<?php

namespace Tests\Unit\Application\Shared;

use App\Application\Shared\Results\OperationResult;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperationResultTest extends TestCase
{
    #[Test]
    public function success_is_reported_as_succeeded(): void
    {
        $this->assertTrue(OperationResult::Success->succeeded());
    }

    #[Test]
    public function failure_results_are_not_succeeded(): void
    {
        $this->assertFalse(OperationResult::NotAllowed->succeeded());
        $this->assertFalse(OperationResult::HasDependents->succeeded());
    }
}
