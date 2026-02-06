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
