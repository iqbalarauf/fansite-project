<?php

namespace App\Support;

use App\Models\ShowTeaterCategories;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Audit historis `show_teater` terhadap `show_teater_categories`.
 *
 * Pencocokan memakai `name` ATAU `jp_name` (tanpa membedakan besar-kecil huruf/
 * spasi), karena data hasil scrape kadang menyimpan judul Jepang pada `unit_song`.
 */
final class ShowTeaterMappingAudit
{
    /**
     * @return array{
     *     scanned_shows: int,
     *     shows_with_unit_song: int,
     *     double_unit_song_rows: int,
     *     setlist: array{matched: int, matched_by_jp_name: int, unmatched: int, unmatched_distinct: int},
     *     unit_song: array{total_tokens: int, matched_scoped: int, matched_by_jp_name: int, matched_global_only: int, unmatched: int, unmatched_distinct: int},
     *     unmatched_setlists: array<int, array{name: string, count: int}>,
     *     unmatched_unit_songs: array<int, array{name: string, count: int}>,
     *     ambiguous_unit_song_names: array<int, array{name: string, setlist_count: int}>
     * }
     */
    public function build(): array
    {
        $categories = ShowTeaterCategories::query()->get(['id', 'type', 'name', 'jp_name', 'setlist_id']);

        $setlistByName = [];
        $setlistByJp = [];
        $unitByName = [];
        $unitByJp = [];
        $unitSetlists = [];

        foreach ($categories as $category) {
            $nameKey = $this->normalize((string) $category->name);
            $jpKey = $this->normalize((string) $category->jp_name);

            if ($category->type === ShowTeaterCategories::TYPE_SETLIST) {
                $setlistByName[$nameKey] = (int) $category->id;

                if ($jpKey !== '') {
                    $setlistByJp[$jpKey] = (int) $category->id;
                }

                continue;
            }

            if ($category->type !== ShowTeaterCategories::TYPE_UNIT_SONG) {
                continue;
            }

            $setlistId = (int) $category->setlist_id;
            $unitByName[$setlistId][$nameKey] = true;

            if ($jpKey !== '') {
                $unitByJp[$setlistId][$jpKey] = true;
            }

            $unitSetlists[$nameKey][$setlistId] = true;
        }

        $shows = DB::table('show_teater')
            ->whereNull('deleted_at')
            ->get(['show_id', 'setlist', 'unit_song']);

        $setlistMatched = 0;
        $setlistMatchedByJp = 0;
        $withUnitSong = 0;
        $doubleUnitSong = 0;
        $unitTokens = 0;
        $unitMatchedScoped = 0;
        $unitMatchedByJp = 0;
        $unitMatchedGlobalOnly = 0;
        $unmatchedSetlists = [];
        $unmatchedUnitSongs = [];

        foreach ($shows as $show) {
            $rawSetlist = trim((string) $show->setlist);
            $setlistKey = $this->normalize($rawSetlist);
            $setlistId = $setlistByName[$setlistKey] ?? null;

            if ($setlistId !== null) {
                $setlistMatched++;
            } elseif (isset($setlistByJp[$setlistKey])) {
                $setlistId = $setlistByJp[$setlistKey];
                $setlistMatched++;
                $setlistMatchedByJp++;
            } else {
                $unmatchedSetlists[$rawSetlist] = ($unmatchedSetlists[$rawSetlist] ?? 0) + 1;
            }

            $tokens = $this->splitUnitSongs((string) $show->unit_song);

            if ($tokens !== []) {
                $withUnitSong++;
            }

            if (count($tokens) > 1) {
                $doubleUnitSong++;
            }

            foreach ($tokens as $token) {
                $unitTokens++;
                $key = $this->normalize($token);
                $scopedByName = $setlistId !== null && isset($unitByName[$setlistId][$key]);
                $scopedByJp = $setlistId !== null && isset($unitByJp[$setlistId][$key]);
                $globalByName = $this->existsGlobally($unitByName, $key);
                $globalByJp = $this->existsGlobally($unitByJp, $key);

                if ($scopedByName || $scopedByJp) {
                    $unitMatchedScoped++;

                    if (! $scopedByName && $scopedByJp) {
                        $unitMatchedByJp++;
                    }
                } elseif ($globalByName || $globalByJp) {
                    $unitMatchedGlobalOnly++;
                } else {
                    $unmatchedUnitSongs[$token] = ($unmatchedUnitSongs[$token] ?? 0) + 1;
                }
            }
        }

        arsort($unmatchedSetlists);
        arsort($unmatchedUnitSongs);

        return [
            'scanned_shows' => $shows->count(),
            'shows_with_unit_song' => $withUnitSong,
            'double_unit_song_rows' => $doubleUnitSong,
            'setlist' => [
                'matched' => $setlistMatched,
                'matched_by_jp_name' => $setlistMatchedByJp,
                'unmatched' => array_sum($unmatchedSetlists),
                'unmatched_distinct' => count($unmatchedSetlists),
            ],
            'unit_song' => [
                'total_tokens' => $unitTokens,
                'matched_scoped' => $unitMatchedScoped,
                'matched_by_jp_name' => $unitMatchedByJp,
                'matched_global_only' => $unitMatchedGlobalOnly,
                'unmatched' => array_sum($unmatchedUnitSongs),
                'unmatched_distinct' => count($unmatchedUnitSongs),
            ],
            'unmatched_setlists' => $this->toRows($unmatchedSetlists),
            'unmatched_unit_songs' => $this->toRows($unmatchedUnitSongs),
            'ambiguous_unit_song_names' => $this->ambiguous($unitSetlists),
        ];
    }

    /**
     * @param  array<int, array<string, bool>>  $map
     */
    private function existsGlobally(array $map, string $key): bool
    {
        foreach ($map as $setlistId => $entries) {
            if ($setlistId !== 0 && isset($entries[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, int>  $map
     * @return array<int, array{name: string, count: int}>
     */
    private function toRows(array $map): array
    {
        $rows = [];

        foreach ($map as $name => $count) {
            $rows[] = ['name' => $name, 'count' => $count];
        }

        return $rows;
    }

    /**
     * @param  array<string, array<int, bool>>  $unitSetlists
     * @return array<int, array{name: string, setlist_count: int}>
     */
    private function ambiguous(array $unitSetlists): array
    {
        $rows = [];

        foreach ($unitSetlists as $name => $setlistIds) {
            $setlistIds = array_filter(array_keys($setlistIds), static fn (int $id): bool => $id !== 0);

            if (count($setlistIds) > 1) {
                $rows[] = ['name' => $name, 'setlist_count' => count($setlistIds)];
            }
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function splitUnitSongs(string $value): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\s*;\s*/', $value) ?: []),
            static fn (string $song): bool => $song !== '',
        ));
    }

    private function normalize(string $value): string
    {
        return Str::lower(trim(preg_replace('/\s+/u', ' ', $value) ?? ''));
    }
}
