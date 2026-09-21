<?php

use App\Models\TheaterReference;
use App\Support\Timezone;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:fetch-streaming-info')->everyTenMinutes()->withoutOverlapping();

Schedule::command('app:sync-google-sheets')->hourly()->withoutOverlapping();

// Reset bulanan reference teater (reference yang dipakai show belum lewat dipertahankan).
Schedule::call(function (): void {
    $now = Timezone::nowLocal();

    TheaterReference::deleteOldReferences($now->month, $now->year);
})->monthlyOn(1, '00:05')->name('theater-references:reset')->withoutOverlapping();
