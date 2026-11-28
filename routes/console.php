<?php

use App\Models\TheaterReference;
use App\Support\Timezone;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Detail live streaming (/api/recent) cukup dipanggil dua kali sehari.
Schedule::command('app:fetch-streaming-info')->twiceDaily(0, 12)->withoutOverlapping();

// Status live (/api/now_live) dipanggil setiap 10 menit.
Schedule::command('app:check-member-live')->everyTenMinutes()->withoutOverlapping();

// Data show teater disinkronkan sekali sehari.
Schedule::command('app:fetch-theater-shows')->daily()->withoutOverlapping();

// Rekaman live oshimen: deteksi live baru, hentikan yang selesai, lalu bersihkan
// rekaman lama. Command tetap aman dijalankan saat fitur dinonaktifkan.
Schedule::command('app:record-member-live')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('app:finalize-live-recordings')->everyMinute()->withoutOverlapping();
Schedule::command('app:prune-live-recordings')->dailyAt('03:15')->withoutOverlapping();

Schedule::command('app:sync-google-sheets')->hourly()->withoutOverlapping();

// Reset bulanan reference teater (reference yang dipakai show belum lewat dipertahankan).
Schedule::call(function (): void {
    $now = Timezone::nowLocal();

    TheaterReference::deleteOldReferences($now->month, $now->year);
})->monthlyOn(1, '00:05')->name('theater-references:reset')->withoutOverlapping();
