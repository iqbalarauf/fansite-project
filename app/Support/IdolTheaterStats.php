<?php

namespace App\Support;

use App\Models\ShowTeater;
use App\Models\ShowTeaterCategories;
use Illuminate\Support\Str;

final class IdolTheaterStats
{
    /**
     * Aggregate theater statistics for the idol profile page.
     *
     * @return array{
     *     year: int,
     *     setlists: array<int, array{name: string, jp_name: ?string, count: int, is_active: bool}>,
     *     unit_songs: array<int, array{name: string, jp_name: ?string, setlists: array<int, string>, count_all: int, count_year: int, on_going: bool}>,
     *     global_center: array{count_all: int, count_year: int, setlists_all: array<int, string>, setlists_year: array<int, string>},
     *     us_center: array{count_all: int, count_year: int, setlists_all: array<int, string>, setlists_year: array<int, string>}
     * }
     */
    public function build(): array
    {
        $year = (int) Timezone::nowLocal()->year;

        $shows = ShowTeater::query()
            ->with(['setlistCategory:id,name', 'unitSongCategories:id,name'])
            ->get(['show_id', 'show_date', 'setlist', 'setlist_id', 'unit_song', 'is_member_show', 'is_global_center', 'is_us_center']);

        $setlistCategories = ShowTeaterCategories::query()
            ->setlists()
            ->get(['name', 'jp_name', 'is_active']);

        $activeLookup = [];
        $setlistJp = [];
        foreach ($setlistCategories as $category) {
            $key = $this->normalize((string) $category->name);
            $setlistJp[$key] = $category->jp_name;
            if ($category->is_active) {
                $activeLookup[$key] = true;
            }
        }

        $unitLookup = $this->unitCategoryLookup();

        $setlistCounts = [];
        $unitCounts = [];
        $globalCenter = ['count_all' => 0, 'count_year' => 0, 'setlists_all' => [], 'setlists_year' => []];
        $usCenter = ['count_all' => 0, 'count_year' => 0, 'setlists_all' => [], 'setlists_year' => []];

        foreach ($shows as $show) {
            $setlist = trim((string) $show->setlistName());
            $key = $this->normalize($setlist);
            $date = ShowDate::normalize((string) $show->show_date);
            $isThisYear = (int) substr($date, 0, 4) === $year;

            if ($setlist !== '') {
                $setlistCounts[$key]['name'] = $setlist;
                $setlistCounts[$key]['count'] = ($setlistCounts[$key]['count'] ?? 0) + 1;

                if ((int) $show->is_global_center === 1) {
                    $globalCenter['count_all']++;
                    $globalCenter['setlists_all'][$setlist] = true;
                    if ($isThisYear) {
                        $globalCenter['count_year']++;
                        $globalCenter['setlists_year'][$setlist] = true;
                    }
                }

                if ((int) $show->is_us_center === 1) {
                    $usCenter['count_all']++;
                    $usCenter['setlists_all'][$setlist] = true;
                    if ($isThisYear) {
                        $usCenter['count_year']++;
                        $usCenter['setlists_year'][$setlist] = true;
                    }
                }
            }

            foreach ($show->unitSongNames() as $raw) {
                $resolved = $unitLookup[$this->normalize($raw)] ?? ['name' => $raw, 'jp_name' => null];
                $songKey = $resolved['name'];

                if (! isset($unitCounts[$songKey])) {
                    $unitCounts[$songKey] = [
                        'name' => $resolved['name'],
                        'jp_name' => $resolved['jp_name'],
                        'setlists' => [],
                        'count_all' => 0,
                        'count_year' => 0,
                        'on_going' => false,
                    ];
                }

                // Setlist tempat unit song ini benar-benar dibawakan (dari data show).
                if ($setlist !== '') {
                    $unitCounts[$songKey]['setlists'][$setlist] = true;
                }

                $unitCounts[$songKey]['count_all']++;
                if ($isThisYear) {
                    $unitCounts[$songKey]['count_year']++;
                }
            }
        }

        // "On Going": for each active setlist, take the unit songs from the latest show_teater
        // row that has a setlist name matching the active setlist. If that latest row has a
        // NULL `is_member_show`, fall back to the previous row of the same setlist.
        $activeShows = [];
        foreach ($shows as $show) {
            $key = $this->normalize((string) $show->setlistName());

            if (isset($activeLookup[$key])) {
                $activeShows[$key][] = $show;
            }
        }

        foreach ($activeShows as $group) {
            $latest = collect($group)
                ->sortByDesc(fn ($show): string => ShowDate::normalize((string) $show->show_date))
                ->first(fn ($show): bool => $show->is_member_show !== null);

            if (! $latest) {
                continue;
            }

            foreach ($latest->unitSongNames() as $raw) {
                $songKey = ($unitLookup[$this->normalize($raw)] ?? ['name' => $raw])['name'];

                if (isset($unitCounts[$songKey])) {
                    $unitCounts[$songKey]['on_going'] = true;
                }
            }
        }

        $setlists = collect($setlistCounts)
            ->map(fn (array $item, string $key): array => [
                'name' => $item['name'],
                'jp_name' => $setlistJp[$key] ?? null,
                'count' => $item['count'],
                'is_active' => isset($activeLookup[$key]),
            ])
            ->sortBy([['is_active', 'desc'], ['count', 'desc']])
            ->values()
            ->all();

        $unitSongs = collect($unitCounts)
            ->map(function (array $song): array {
                $song['setlists'] = array_values(array_keys($song['setlists']));

                return $song;
            })
            ->sortBy([['on_going', 'desc'], ['count_all', 'desc']])
            ->values()
            ->all();

        return [
            'year' => $year,
            'setlists' => $setlists,
            'unit_songs' => $unitSongs,
            'global_center' => [
                'count_all' => $globalCenter['count_all'],
                'count_year' => $globalCenter['count_year'],
                'setlists_all' => array_keys($globalCenter['setlists_all']),
                'setlists_year' => array_keys($globalCenter['setlists_year']),
            ],
            'us_center' => [
                'count_all' => $usCenter['count_all'],
                'count_year' => $usCenter['count_year'],
                'setlists_all' => array_keys($usCenter['setlists_all']),
                'setlists_year' => array_keys($usCenter['setlists_year']),
            ],
        ];
    }

    /**
     * Lookup unit song category name/jp_name by its name or Japanese name.
     *
     * @return array<string, array{name: string, jp_name: ?string}>
     */
    private function unitCategoryLookup(): array
    {
        $lookup = [];

        foreach (ShowTeaterCategories::query()->unitSongs()->get(['name', 'jp_name']) as $category) {
            $entry = ['name' => (string) $category->name, 'jp_name' => $category->jp_name];
            $lookup[$this->normalize((string) $category->name)] = $entry;

            if ($category->jp_name) {
                $lookup[$this->normalize((string) $category->jp_name)] = $entry;
            }
        }

        return $lookup;
    }

    private function normalize(string $value): string
    {
        return Str::lower(trim($value));
    }
}
