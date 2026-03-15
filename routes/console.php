<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('alerts:evaluate')->dailyAt('01:30');
Schedule::command('billing:mark-overdue')->dailyAt('01:45');
Schedule::command('backup:run')->dailyAt('02:00');
Schedule::command('backup:prune')->dailyAt('02:20');
