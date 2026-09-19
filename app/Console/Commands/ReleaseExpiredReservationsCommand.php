<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class ReleaseExpiredReservationsCommand extends Command
{
    protected $signature = 'shoeboy:release-expired';
    protected $description = 'I-release ang mga expired reservation balik sa available stock';

    public function handle(OrderService $orderService): int
    {
        $released = $orderService->releaseExpiredReservations();
        $this->info("Released {$released} expired reservation(s) back to available stock.");

        return self::SUCCESS;
    }
}
