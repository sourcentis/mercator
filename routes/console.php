<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan commands for manual execution
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Scheduled tasks
Schedule::command('mercator:cleanup')
    ->name('cleanup-daily')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        info('Cleanup task completed successfully');
    })
    ->onFailure(function () {
        logger()->error('Cleanup task failed');
    });

Schedule::command('mercator:certificate-expiration')
    ->name('certificate-expiration-daily')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('mercator:cve-search')
    ->name('cve-search-daily')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground()
    ->environments(['production']); // Uniquement en production si nécessaire

Schedule::command('mercator:cpe-sync')
    ->name('cpe-sync-daily')
    ->dailyAt('00:30')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('license:check')
    ->name('validate-license-daily')
    ->dailyAt('00:30')
    ->withoutOverlapping(60)
    ->runInBackground();

Schedule::command('mercator:remind-cartographers')
    ->name('remind-cartographers-daily')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();
