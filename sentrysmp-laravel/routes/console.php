<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// G7: re-queue any "delivered" commands the game server fetched but never acknowledged
Schedule::command('commands:retry-stale')->everyFiveMinutes();
