<?php

namespace App\Support;

use App\Models\LiveStreaming;
use App\Models\ShowTeater;
use Illuminate\Database\Eloquent\Builder;

class CustomPageStatistic
{
    public static function value(array $data): int
    {
        $metric = $data['metric'] ?? '';

        return match ($metric) {
            'show_teater_all' => ShowTeater::query()->count(),
            'show_teater_date_range' => self::showTeaterDateRange($data)->count(),
            'show_teater_setlist' => self::showTeaterSetlist($data)->count(),
            'unit_song_all' => ShowTeater::query()->whereNotNull('unit_song')->where('unit_song', '!=', '')->count(),
            'unit_song_date_range' => self::showTeaterDateRange($data)->whereNotNull('unit_song')->where('unit_song', '!=', '')->count(),
            'unit_song_setlist' => self::showTeaterSetlist($data)->whereNotNull('unit_song')->where('unit_song', '!=', '')->count(),
            'center_unit_song_all' => self::unitSongCenters()->count(),
            'center_unit_song_unit_song' => self::unitSongCenters()->when($data['unit_song'] ?? null, fn (Builder $query, string $unitSong): Builder => $query->where('unit_song', $unitSong))->count(),
            'center_unit_song_setlist' => self::unitSongCenters()->when($data['setlist'] ?? null, fn (Builder $query, string $setlist): Builder => $query->where('setlist', $setlist))->count(),
            'center_unit_song_date_range' => self::showTeaterDateRange($data)->whereNotNull('is_us_center')->count(),
            'global_center_date_range' => self::showTeaterDateRange($data)->where('is_global_center', 1)->count(),
            'global_center_setlist' => self::showTeaterSetlist($data)->where('is_global_center', 1)->count(),
            'live_streaming_time' => self::liveStreamingDateRange($data)->count(),
            'live_streaming_row' => LiveStreaming::query()->count(),
            'live_streaming_platform' => LiveStreaming::query()->where('platform', $data['platform'] ?? '')->count(),
            default => 0,
        };
    }

    private static function showTeaterDateRange(array $data): Builder
    {
        return ShowTeater::query()
            ->when($data['date_from'] ?? null, fn (Builder $query, string $dateFrom): Builder => $query->whereDate('show_date', '>=', $dateFrom))
            ->when($data['date_to'] ?? null, fn (Builder $query, string $dateTo): Builder => $query->whereDate('show_date', '<=', $dateTo));
    }

    private static function showTeaterSetlist(array $data): Builder
    {
        return ShowTeater::query()
            ->when($data['setlist'] ?? null, fn (Builder $query, string $setlist): Builder => $query->where('setlist', $setlist));
    }

    private static function unitSongCenters(): Builder
    {
        return ShowTeater::query()->whereNotNull('is_us_center');
    }

    private static function liveStreamingDateRange(array $data): Builder
    {
        return LiveStreaming::query()
            ->when($data['date_from'] ?? null, fn (Builder $query, string $dateFrom): Builder => $query->whereDate('live_date', '>=', $dateFrom))
            ->when($data['date_to'] ?? null, fn (Builder $query, string $dateTo): Builder => $query->whereDate('live_date', '<=', $dateTo));
    }
}
