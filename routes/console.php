<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule JKT48 scraper to run every Tuesday at 9:00 AM
Schedule::command('app:scrape-jkt48-schedule')
    ->weekly()
    ->tuesdays()
    ->at('18:00')
    ->timezone('Asia/Jakarta');
