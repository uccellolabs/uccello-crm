<?php

namespace App\Application\Deals\UseCases;

use App\Domain\Deals\Repositories\DealRepositoryInterface;
use App\Models\Deal;

class DeleteDeal
{
    public function __construct(
        private readonly DealRepositoryInterface $deals,
    ) {}

    public function handle(Deal $deal): void
    {
        $this->deals->delete($deal);
    }
}
