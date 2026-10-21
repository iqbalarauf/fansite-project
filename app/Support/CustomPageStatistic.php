<?php

namespace App\Support;

use App\Models\LiveStreaming;
use App\Models\ShowTeater;
use App\Models\ShowTeaterCategories;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomPageStatistic
{
    /**
     * Hasil terstruktur: count (angka) atau list (daftar nama + jumlah).
     *
     * @param  array<string, mixed>  $data
     * @return array{type: string, value: int, items: array<int, array{label: string, count: int|null}>}
     */
    public static function result(array $data): array
    {
        $metric = (string) ($data['metric'] ?? '');

        return match ($metric) {
            // Show Teater — count
            'show_teater_all' => self::count(ShowTeater::query()->count()),
            'show_teater_date_range' => self::count(self::showTeaterDateRange($data)->count()),
            'show_teater_setlist' => self::count(self::showTeaterSetlist($data)->count()),
            'show_teater_all_setlist' => self::count(self::showsWithSetlist(ShowTeater::query())->count()),
            'show_teater_setlist_count_date_range' => self::count(self::countDistinctSetlists(self::showTeaterDateRange($data))),
            'show_teater_active_setlist_count' => self::count(self::activeSetlists()->distinct()->count('name')),

            // Show Teater — list
            'show_teater_active_setlist_list' => self::list(self::activeSetlistBreakdown()),
            'show_teater_all_setlist_list' => self::list(self::masterSetlistItems(false)),
            'show_teater_all_setlist_list_count' => self::list(self::masterSetlistItems(true)),
            'show_teater_show_list_date_range' => self::list(self::showListInDateRange($data)),

            // Unit Song — count
            'unit_song_all' => self::count(self::showsWithUnitSong(ShowTeater::query())->count()),
            'unit_song_date_range' => self::count(self::showsWithUnitSong(self::showTeaterDateRange($data))->count()),
            'unit_song_setlist' => self::count(self::showsWithUnitSong(self::showTeaterSetlist($data))->count()),
            'unit_song_active_count' => self::count(self::activeUnitSongs()->distinct()->count('name')),

            // Unit Song — list
            'unit_song_active_list' => self::list(self::activeUnitSongBreakdown()),
            'unit_song_all_list' => self::list(self::masterUnitSongItems(false)),
            'unit_song_all_list_count' => self::list(self::masterUnitSongItems(true)),
            'unit_song_show_list_date_range' => self::list(self::showWithUnitSongsInDateRange($data)),

            // Center Unit Song
            'center_unit_song_all' => self::count(self::unitSongCenters()->count()),
            'center_unit_song_unit_song' => self::count(self::unitSongCenters()->when($data['unit_song'] ?? null, fn (Builder $query, string $unitSong): Builder => $query->where('unit_song', $unitSong))->count()),
            'center_unit_song_setlist' => self::count(self::unitSongCenters()->when($data['setlist'] ?? null, fn (Builder $query, string $setlist): Builder => $query->where('setlist', $setlist))->count()),
            'center_unit_song_date_range' => self::count(self::showTeaterDateRange($data)->whereNotNull('is_us_center')->count()),

            // Global Center
            'global_center_date_range' => self::count(self::showTeaterDateRange($data)->where('is_global_center', 1)->count()),
            'global_center_setlist' => self::count(self::showTeaterSetlist($data)->where('is_global_center', 1)->count()),

            // Live Streaming
            'live_streaming_time' => self::count(self::liveStreamingDateRange($data)->count()),
            'live_streaming_row' => self::count(LiveStreaming::query()->count()),
            'live_streaming_platform' => self::count(LiveStreaming::query()->where('platform', $data['platform'] ?? '')->count()),

            default => self::count(0),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function value(array $data): int
    {
        return self::result($data)['value'];
    }

    /**
     * @return array{type: string, value: int, items: array<int, array{label: string, count: int|null}>}
     */
    private static function count(int $value): array
    {
        return ['type' => 'count', 'value' => $value, 'items' => []];
    }

    /**
     * @param  array<int, array{label: string, count: int|null}>  $items
     * @return array{type: string, value: int, items: array<int, array{label: string, count: int|null}>}
     */
    private static function list(array $items): array
    {
        return ['type' => 'list', 'value' => count($items), 'items' => array_values($items)];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function showTeaterDateRange(array $data): Builder
    {
        return ShowTeater::query()
            ->when($data['date_from'] ?? null, fn (Builder $query, string $dateFrom): Builder => $query->whereDate('show_date', '>=', $dateFrom))
            ->when($data['date_to'] ?? null, fn (Builder $query, string $dateTo): Builder => $query->whereDate('show_date', '<=', $dateTo));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function showTeaterSetlist(array $data): Builder
    {
        return ShowTeater::query()
            ->when($data['setlist'] ?? null, fn (Builder $query, string $setlist): Builder => $query->where('setlist', $setlist));
    }

    private static function showsWithSetlist(Builder $query): Builder
    {
        return $query->whereNotNull('setlist')->where('setlist', '!=', '');
    }

    private static function showsWithUnitSong(Builder $query): Builder
    {
        return $query->whereNotNull('unit_song')->where('unit_song', '!=', '');
    }

    private static function unitSongCenters(): Builder
    {
        return ShowTeater::query()->whereNotNull('is_us_center');
    }

    private static function activeSetlists(): Builder
    {
        return ShowTeaterCategories::query()->setlists()->active();
    }

    private static function activeUnitSongs(): Builder
    {
        return ShowTeaterCategories::query()->unitSongs()->active();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function liveStreamingDateRange(array $data): Builder
    {
        return LiveStreaming::query()
            ->when($data['date_from'] ?? null, fn (Builder $query, string $dateFrom): Builder => $query->whereDate('live_date', '>=', $dateFrom))
            ->when($data['date_to'] ?? null, fn (Builder $query, string $dateTo): Builder => $query->whereDate('live_date', '<=', $dateTo));
    }

    private static function countDistinctSetlists(Builder $query): int
    {
        return self::showsWithSetlist($query)->distinct()->count('setlist');
    }

    /**
     * Setlist yang ditandai aktif pada katalog, beserta jumlah show pemakaiannya.
     *
     * @return array<int, array{label: string, count: int|null}>
     */
    private static function activeSetlistBreakdown(): array
    {
        $counts = self::setlistShowCounts();

        return self::activeSetlists()
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values()
            ->map(fn ($name): array => ['label' => (string) $name, 'count' => (int) ($counts[$name] ?? 0)])
            ->all();
    }

    /**
     * @return Collection<string, int>
     */
    private static function setlistShowCounts(): Collection
    {
        return self::showsWithSetlist(ShowTeater::query())
            ->selectRaw('setlist as name, COUNT(*) as total')
            ->groupBy('setlist')
            ->pluck('total', 'name');
    }

    /**
     * @return array<int, array{label: string, count: int|null}>
     */
    private static function masterSetlistItems(bool $withCount): array
    {
        $counts = $withCount
            ? self::showsWithSetlist(ShowTeater::query())
                ->selectRaw('setlist as name, COUNT(*) as total')
                ->groupBy('setlist')
                ->pluck('total', 'name')
            : collect();

        return ShowTeaterCategories::query()
            ->setlists()
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name): array => ['label' => (string) $name, 'count' => $withCount ? (int) ($counts[$name] ?? 0) : null])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{label: string, count: int|null}>
     */
    private static function showListInDateRange(array $data): array
    {
        return self::showTeaterDateRange($data)
            ->orderBy('show_date')
            ->get(['show_date', 'setlist'])
            ->map(fn (ShowTeater $show): array => [
                'label' => trim(((string) $show->show_date).' · '.((string) ($show->setlist ?? ''))),
                'count' => null,
            ])
            ->all();
    }

    /**
     * Unit song yang ditandai aktif pada katalog, beserta jumlah show pemakaiannya (via pivot).
     *
     * @return array<int, array{label: string, count: int|null}>
     */
    private static function activeUnitSongBreakdown(): array
    {
        $counts = self::unitSongShowCounts();

        return self::activeUnitSongs()
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values()
            ->map(fn ($name): array => ['label' => (string) $name, 'count' => (int) ($counts[$name] ?? 0)])
            ->all();
    }

    /**
     * @return Collection<string, int>
     */
    private static function unitSongShowCounts(): Collection
    {
        return DB::table('show_teater_unit_song as pivot')
            ->join('show_teater as shows', 'shows.show_id', '=', 'pivot.show_id')
            ->join('show_teater_categories as categories', 'categories.id', '=', 'pivot.show_teater_categories_id')
            ->whereNull('shows.deleted_at')
            ->where('categories.type', ShowTeaterCategories::TYPE_UNIT_SONG)
            ->selectRaw('categories.name as name, COUNT(DISTINCT pivot.show_id) as total')
            ->groupBy('categories.name')
            ->pluck('total', 'name');
    }

    /**
     * @return array<int, array{label: string, count: int|null}>
     */
    private static function masterUnitSongItems(bool $withCount): array
    {
        $counts = $withCount
            ? self::unitSongShowCounts()
            : collect();

        return ShowTeaterCategories::query()
            ->unitSongs()
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name): array => ['label' => (string) $name, 'count' => $withCount ? (int) ($counts[$name] ?? 0) : null])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{label: string, count: int|null}>
     */
    private static function showWithUnitSongsInDateRange(array $data): array
    {
        return self::showTeaterDateRange($data)
            ->with('unitSongCategories')
            ->orderBy('show_date')
            ->get()
            ->map(function (ShowTeater $show): array {
                $songs = array_values(array_filter(array_map('trim', $show->unitSongNames())));
                $detail = $songs === [] ? '' : ' · '.implode(', ', $songs);

                return [
                    'label' => trim(((string) $show->show_date).' · '.((string) ($show->setlist ?? '')).$detail),
                    'count' => count($songs) ?: null,
                ];
            })
            ->all();
    }
}
