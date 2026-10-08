<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('shoeboy:release-expired')->everyMinute()->withoutOverlapping();

// Keep a rolling local snapshot of the database.
Schedule::command('shoeboy:backup')->dailyAt('23:00')->withoutOverlapping();
