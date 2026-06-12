<?php

namespace Tests\Unit\Application\Deals;

use App\Application\Deals\Commands\CreateDealCommand;
use App\Application\Deals\UseCases\CreateDeal;
use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Domain\Shared\Enums\DealStatus;
use App\Models\Deal;
use App\Models\PipelineStage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateDealTest extends TestCase
{
    #[Test]
    public function it_assigns_the_next_position_for_the_target_stage(): void
    {
        $stage = new PipelineStage(['is_won' => false, 'is_lost' => false]);
        $stage->id = 3;

        $created = new Deal(['name' => 'Big deal']);

        $deals = $this->createMock(DealRepositoryInterface::class);
        $deals->expects($this->once())->method('nextPosition')->with(3)->willReturn(4);
        $deals->expects($this->once())->method('create')->with(
            $this->callback(fn (array $data) => $data['position'] === 4 && $data['name'] === 'Big deal'),
        )->willReturn($created);

        $command = CreateDealCommand::fromFormInput(name: 'Big deal', pipelineId: 1, pipelineStageId: 3);
        $result = (new CreateDeal($deals))->handle($command, $stage);

        $this->assertSame($created, $result);
    }

    #[Test]
    public function it_sets_open_status_for_non_terminal_stages(): void
    {
        $stage = new PipelineStage(['is_won' => false, 'is_lost' => false]);
        $stage->id = 1;

        $deals = $this->createMock(DealRepositoryInterface::class);
        $deals->method('nextPosition')->willReturn(0);
        $deals->expects($this->once())->method('create')->with(
            $this->callback(fn (array $data) => $data['status'] === DealStatus::Open && ! array_key_exists('closed_at', $data)),
        )->willReturn(new Deal);

        (new CreateDeal($deals))->handle(
            CreateDealCommand::fromFormInput(name: 'Deal', pipelineId: 1, pipelineStageId: 1),
            $stage,
        );
    }

    #[Test]
    public function it_sets_won_status_when_created_in_a_won_stage(): void
    {
        $stage = new PipelineStage(['is_won' => true, 'is_lost' => false]);
        $stage->id = 2;

        $deals = $this->createMock(DealRepositoryInterface::class);
        $deals->method('nextPosition')->willReturn(0);
        $deals->expects($this->once())->method('create')->with(
            $this->callback(fn (array $data) => $data['status'] === DealStatus::Won && $data['closed_at'] !== null),
        )->willReturn(new Deal);

        (new CreateDeal($deals))->handle(
            CreateDealCommand::fromFormInput(name: 'Won deal', pipelineId: 1, pipelineStageId: 2),
            $stage,
        );
    }

    #[Test]
    public function it_sets_lost_status_when_created_in_a_lost_stage(): void
    {
        $stage = new PipelineStage(['is_won' => false, 'is_lost' => true]);
        $stage->id = 5;

        $deals = $this->createMock(DealRepositoryInterface::class);
        $deals->method('nextPosition')->willReturn(0);
        $deals->expects($this->once())->method('create')->with(
            $this->callback(fn (array $data) => $data['status'] === DealStatus::Lost && $data['closed_at'] !== null),
        )->willReturn(new Deal);

        (new CreateDeal($deals))->handle(
            CreateDealCommand::fromFormInput(name: 'Lost deal', pipelineId: 1, pipelineStageId: 5),
            $stage,
        );
    }
}
