<?php

namespace Tests\Unit\Application\Pipelines;

use App\Domain\Pipelines\Enums\PipelineStageDeletionBlockReason;
use App\Domain\Pipelines\Services\PipelineStageDeletionPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PipelineStageDeletionPolicyTest extends TestCase
{
    private PipelineStageDeletionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new PipelineStageDeletionPolicy;
    }

    #[Test]
    public function it_allows_deletion_of_a_regular_stage_without_deals(): void
    {
        $this->assertNull($this->policy->evaluate(isWon: false, isLost: false, hasDeals: false));
    }

    #[Test]
    public function it_blocks_deletion_of_a_won_stage(): void
    {
        $this->assertSame(
            PipelineStageDeletionBlockReason::TerminalStage,
            $this->policy->evaluate(isWon: true, isLost: false, hasDeals: false),
        );
    }

    #[Test]
    public function it_blocks_deletion_of_a_lost_stage(): void
    {
        $this->assertSame(
            PipelineStageDeletionBlockReason::TerminalStage,
            $this->policy->evaluate(isWon: false, isLost: true, hasDeals: false),
        );
    }

    #[Test]
    public function it_blocks_deletion_when_stage_has_deals(): void
    {
        $this->assertSame(
            PipelineStageDeletionBlockReason::HasDeals,
            $this->policy->evaluate(isWon: false, isLost: false, hasDeals: true),
        );
    }

    #[Test]
    public function it_prioritises_terminal_stage_over_has_deals(): void
    {
        $this->assertSame(
            PipelineStageDeletionBlockReason::TerminalStage,
            $this->policy->evaluate(isWon: true, isLost: false, hasDeals: true),
        );
    }
}
