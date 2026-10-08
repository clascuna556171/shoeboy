<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('shoeboy:release-expired')->everyMinute()->withoutOverlapping();

// Keep a rolling local snapshot of the database.
Schedule::command('shoeboy:backup')->dailyAt('23:00')->withoutOverlapping();
