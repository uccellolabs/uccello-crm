<?php

namespace Tests\Unit\Application\Pipelines;

use App\Application\Pipelines\UseCases\DeletePipelineStage;
use App\Application\Shared\Results\DeletionResult;
use App\Domain\Pipelines\Repositories\PipelineStageRepositoryInterface;
use App\Domain\Pipelines\Services\PipelineStageDeletionPolicy;
use App\Models\PipelineStage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeletePipelineStageTest extends TestCase
{
    #[Test]
    public function it_deletes_an_allowed_stage(): void
    {
        $stage = new PipelineStage(['is_won' => false, 'is_lost' => false]);
        $stage->id = 7;

        $stages = $this->createMock(PipelineStageRepositoryInterface::class);
        $stages->expects($this->once())->method('hasDeals')->with(7)->willReturn(false);
        $stages->expects($this->once())->method('delete')->with($stage);

        $result = (new DeletePipelineStage($stages, new PipelineStageDeletionPolicy))->handle($stage);

        $this->assertSame(DeletionResult::Success, $result);
    }

    #[Test]
    public function it_blocks_terminal_won_stages(): void
    {
        $stage = new PipelineStage(['is_won' => true, 'is_lost' => false]);
        $stage->id = 1;

        $stages = $this->createMock(PipelineStageRepositoryInterface::class);
        $stages->expects($this->once())->method('hasDeals')->with(1)->willReturn(false);
        $stages->expects($this->never())->method('delete');

        $result = (new DeletePipelineStage($stages, new PipelineStageDeletionPolicy))->handle($stage);

        $this->assertSame(DeletionResult::BlockedTerminalStage, $result);
    }

    #[Test]
    public function it_blocks_terminal_lost_stages(): void
    {
        $stage = new PipelineStage(['is_won' => false, 'is_lost' => true]);
        $stage->id = 2;

        $stages = $this->createMock(PipelineStageRepositoryInterface::class);
        $stages->expects($this->once())->method('hasDeals')->with(2)->willReturn(false);
        $stages->expects($this->never())->method('delete');

        $result = (new DeletePipelineStage($stages, new PipelineStageDeletionPolicy))->handle($stage);

        $this->assertSame(DeletionResult::BlockedTerminalStage, $result);
    }

    #[Test]
    public function it_blocks_stages_that_still_have_deals(): void
    {
        $stage = new PipelineStage(['is_won' => false, 'is_lost' => false]);
        $stage->id = 3;

        $stages = $this->createMock(PipelineStageRepositoryInterface::class);
        $stages->expects($this->once())->method('hasDeals')->with(3)->willReturn(true);
        $stages->expects($this->never())->method('delete');

        $result = (new DeletePipelineStage($stages, new PipelineStageDeletionPolicy))->handle($stage);

        $this->assertSame(DeletionResult::BlockedHasDeals, $result);
    }
}
