<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Delete pending companies older than 3 days - runs daily at midnight
Schedule::command('companies:delete-stale-pending --days=3')
    ->daily()
    ->at('00:00')
    ->withoutOverlapping()
    ->runInBackground();

// Auto-approve non-suspicious mobile access requests 10 minutes after creation
Schedule::command('mobile-access:auto-approve')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Subscription lifecycle: status changes and renewal reminders (reminders only send when billing is enforced)
Schedule::command('subscriptions:expire')
    ->daily()
    ->at('00:10')
    ->withoutOverlapping();

Schedule::command('subscriptions:remind')
    ->daily()
    ->at('08:00')
    ->withoutOverlapping();

// Catch payments whose webhook never arrived (does nothing until Selcom credentials are set)
Schedule::command('payments:verify-pending')
    ->everyFiveMinutes()
    ->withoutOverlapping();
