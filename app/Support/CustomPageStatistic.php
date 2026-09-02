<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CustomPageStatistic
{
    public static function value(array $data): int
    {
        $metric = $data['metric'] ?? '';

        return match ($metric) {
            'show_teater_all' => DB::table('show_teater')->count(),
            'show_teater_date_range' => self::showTeaterDateRange($data)->count(),
            'show_teater_setlist' => self::showTeaterSetlist($data)->count(),
            'unit_song_all' => DB::table('show_teater')->whereNotNull('unit_song')->where('unit_song', '!=', '')->count(),
            'unit_song_date_range' => self::showTeaterDateRange($data)->whereNotNull('unit_song')->where('unit_song', '!=', '')->count(),
            'unit_song_setlist' => self::showTeaterSetlist($data)->whereNotNull('unit_song')->where('unit_song', '!=', '')->count(),
            'center_unit_song_all' => self::unitSongCenters()->count(),
            'center_unit_song_unit_song' => self::unitSongCenters()->when($data['unit_song'] ?? null, fn (Builder $query, string $unitSong): Builder => $query->where('unit_song', $unitSong))->count(),
            'center_unit_song_setlist' => self::unitSongCenters()->when($data['setlist'] ?? null, fn (Builder $query, string $setlist): Builder => $query->where('setlist', $setlist))->count(),
            'center_unit_song_date_range' => self::showTeaterDateRange($data)->whereNotNull('is_us_center')->count(),
            'global_center_date_range' => self::showTeaterDateRange($data)->where('is_global_center', 1)->count(),
            'global_center_setlist' => self::showTeaterSetlist($data)->where('is_global_center', 1)->count(),
            'live_streaming_time' => self::liveStreamingDateRange($data)->count(),
            'live_streaming_row' => DB::table('live_streaming')->count(),
            'live_streaming_platform' => DB::table('live_streaming')->where('platform', $data['platform'] ?? '')->count(),
            default => 0,
        };
    }

    private static function showTeaterDateRange(array $data): Builder
    {
        return DB::table('show_teater')
            ->when($data['date_from'] ?? null, fn (Builder $query, string $dateFrom): Builder => $query->whereDate('show_date', '>=', $dateFrom))
            ->when($data['date_to'] ?? null, fn (Builder $query, string $dateTo): Builder => $query->whereDate('show_date', '<=', $dateTo));
    }

    private static function showTeaterSetlist(array $data): Builder
    {
        return DB::table('show_teater')
            ->when($data['setlist'] ?? null, fn (Builder $query, string $setlist): Builder => $query->where('setlist', $setlist));
    }

    private static function unitSongCenters(): Builder
    {
        return DB::table('show_teater')->whereNotNull('is_us_center');
    }

    private static function liveStreamingDateRange(array $data): Builder
    {
        return DB::table('live_streaming')
            ->when($data['date_from'] ?? null, fn (Builder $query, string $dateFrom): Builder => $query->whereDate('live_date', '>=', $dateFrom))
            ->when($data['date_to'] ?? null, fn (Builder $query, string $dateTo): Builder => $query->whereDate('live_date', '<=', $dateTo));
    }
}
