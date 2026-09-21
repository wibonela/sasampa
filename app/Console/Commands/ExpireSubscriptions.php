<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Move lapsed subscriptions to past_due, then expired';

    public function handle(BillingService $billing): int
    {
        $this->info('Updated ' . $billing->expireDue() . ' subscription(s).');

        return self::SUCCESS;
    }
}
