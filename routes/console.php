<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:generate-doctor-schedules')
    ->dailyAt('00:00');

Schedule::command('app:prune-idempotency-keys')
    ->dailyAt('00:00');

Schedule::command('app:send-appointment-reminders')
    ->dailyAt('08:00');

Schedule::command('app:cancel-overdue-appointments')
    ->dailyAt('00:00')
    ->withoutOverlapping();

Schedule::command('app:test-scheduler')
    ->everyMinute();

Schedule::command('app:check-idle-conversations')
    ->everyMinute()
    ->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
