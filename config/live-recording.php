<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Live Recording
    |--------------------------------------------------------------------------
    |
    | Otomatis merekam live oshimen dari /api/now_live saat statusnya online.
    | Fitur ini membutuhkan ffmpeg dan sebaiknya hanya diaktifkan di VPS Linux
    | yang memiliki ruang disk cukup.
    |
    */

    'enabled' => filter_var(env('LIVE_RECORDING_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    /*
    | Disk privat tempat file rekaman disimpan. Jangan gunakan disk "public"
    | karena rekaman hanya boleh diakses oleh admin.
    */
    'disk' => env('LIVE_RECORDING_DISK', 'recordings'),

    /*
    | Direktori (relatif terhadap disk di atas) untuk file .ts, .mp4, dan log.
    */
    'directory' => env('LIVE_RECORDING_DIRECTORY', 'live'),

    'ffmpeg_path' => env('LIVE_RECORDING_FFMPEG_PATH', 'ffmpeg'),

    /*
    | Rekaman yang lebih lama dari jumlah hari ini akan dihapus otomatis.
    */
    'retention_days' => (int) env('LIVE_RECORDING_RETENTION_DAYS', 14),

    /*
    | Batas aman agar proses ffmpeg tidak berjalan selamanya ketika API gagal
    | melaporkan bahwa live sudah berakhir.
    */
    'max_duration_minutes' => (int) env('LIVE_RECORDING_MAX_DURATION_MINUTES', 360),

    /*
    | Setelah sesi live tidak muncul lagi di /api/now_live, beri jeda selama
    | ini sebelum ffmpeg dihentikan. Mencegah live terpotong karena API glitch.
    */
    'finalize_delay_minutes' => (int) env('LIVE_RECORDING_FINALIZE_DELAY_MINUTES', 5),

    /*
    | Jeda setelah SIGINT agar ffmpeg sempat menutup file MPEG-TS dengan rapi
    | sebelum di-remux ke MP4.
    */
    'stop_grace_seconds' => (int) env('LIVE_RECORDING_STOP_GRACE_SECONDS', 20),

    'remux_timeout' => (int) env('LIVE_RECORDING_REMUX_TIMEOUT', 1800),

    /*
    | Beberapa stream membutuhkan User-Agent atau header tambahan. Header
    | ditulis sebagai daftar string, misal ['Referer: https://www.idn.app/'].
    */
    'user_agent' => env('LIVE_RECORDING_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36'),

    'headers' => [],

];
