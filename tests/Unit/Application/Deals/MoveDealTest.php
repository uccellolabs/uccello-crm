<?php

namespace Tests\Unit\Application\Deals;

use App\Application\Deals\Commands\MoveDealCommand;
use App\Application\Deals\UseCases\MoveDeal;
use App\Application\Pipelines\Repositories\PipelineReadRepositoryInterface;
use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Models\Deal;
use App\Models\PipelineStage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MoveDealTest extends TestCase
{
    #[Test]
    public function it_delegates_movement_to_the_repository(): void
    {
        $deal = new Deal(['pipeline_stage_id' => 1]);
        $deal->id = 10;

        $stage = new PipelineStage(['is_won' => false, 'is_lost' => false]);
        $stage->id = 2;

        $moved = new Deal(['pipeline_stage_id' => 2]);
        $moved->id = 10;

        $command = new MoveDealCommand(stageId: 2, position: 0);

        $deals = $this->createMock(DealRepositoryInterface::class);
        $deals->expects($this->once())->method('moveToStage')->with($deal, 2, false, false, 0)->willReturn($moved);

        $pipelineRead = $this->createMock(PipelineReadRepositoryInterface::class);
        $pipelineRead->expects($this->once())->method('findStageOrFail')->with(2)->willReturn($stage);

        $result = (new MoveDeal($deals, $pipelineRead))->handle($deal, $command);

        $this->assertSame($moved, $result);
    }

    #[Test]
    public function it_passes_terminal_won_flags_to_the_repository(): void
    {
        $deal = new Deal;
        $stage = new PipelineStage(['is_won' => true, 'is_lost' => false]);
        $stage->id = 9;

        $deals = $this->createMock(DealRepositoryInterface::class);
        $deals->expects($this->once())->method('moveToStage')->with($deal, 9, true, false, 3)->willReturn(new Deal);

        $pipelineRead = $this->createMock(PipelineReadRepositoryInterface::class);
        $pipelineRead->method('findStageOrFail')->willReturn($stage);

        (new MoveDeal($deals, $pipelineRead))->handle($deal, new MoveDealCommand(stageId: 9, position: 3));
    }

    #[Test]
    public function it_passes_terminal_lost_flags_to_the_repository(): void
    {
        $deal = new Deal;
        $stage = new PipelineStage(['is_won' => false, 'is_lost' => true]);
        $stage->id = 8;

        $deals = $this->createMock(DealRepositoryInterface::class);
        $deals->expects($this->once())->method('moveToStage')->with($deal, 8, false, true, 1)->willReturn(new Deal);

        $pipelineRead = $this->createMock(PipelineReadRepositoryInterface::class);
        $pipelineRead->method('findStageOrFail')->willReturn($stage);

        (new MoveDeal($deals, $pipelineRead))->handle($deal, new MoveDealCommand(stageId: 8, position: 1));
    }

    #[Test]
    public function it_forwards_the_requested_position(): void
    {
        $deal = new Deal;
        $stage = new PipelineStage(['is_won' => false, 'is_lost' => false]);
        $stage->id = 4;

        $deals = $this->createMock(DealRepositoryInterface::class);
        $deals->expects($this->once())->method('moveToStage')->with($deal, 4, false, false, 7)->willReturn(new Deal);

        $pipelineRead = $this->createMock(PipelineReadRepositoryInterface::class);
        $pipelineRead->method('findStageOrFail')->willReturn($stage);

        (new MoveDeal($deals, $pipelineRead))->handle($deal, new MoveDealCommand(stageId: 4, position: 7));
    }
}
