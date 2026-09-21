<?php

namespace App\Http\Controllers;

use App\Models\ShowTeaterCategories;
use App\Support\ShowTeaterNormalizer;
use App\Support\Spreadsheet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ShowTeaterController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $setlistFilter = $request->get('setlist');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $sortBy = $request->get('sort_by', 'show_id');
        $sortDir = $request->get('sort_dir', 'desc');
        $perPage = (int) $request->get('per_page', 10);

        // Validate sortable columns
        $allowedSorts = ['show_id', 'show_date', 'setlist'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'show_id';
        }
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        $query = DB::table('show_teater')
            ->whereNull('show_teater.deleted_at')
            ->leftJoin('show_teater_categories as setlist_cat', function ($join) {
                $join->on('show_teater.setlist', '=', 'setlist_cat.name')
                    ->where('setlist_cat.type', '=', 'setlist');
            })
            ->leftJoin('show_teater_categories as unit_song_cat', function ($join) {
                $join->on('show_teater.unit_song', '=', 'unit_song_cat.name')
                    ->where('unit_song_cat.type', '=', 'unit_song')
                    ->on('unit_song_cat.setlist_id', '=', 'setlist_cat.id');
            })
            ->select(
                'show_teater.*',
                'setlist_cat.jp_name as setlist_jp_name',
                'unit_song_cat.jp_name as unit_song_jp_name',
                DB::raw("CONCAT(show_teater.setlist, IF(setlist_cat.jp_name IS NOT NULL AND setlist_cat.jp_name != '', CONCAT(' (', setlist_cat.jp_name, ')'), '')) as display_setlist"),
                DB::raw("CONCAT(show_teater.unit_song, IF(unit_song_cat.jp_name IS NOT NULL AND unit_song_cat.jp_name != '', CONCAT(' (', unit_song_cat.jp_name, ')'), '')) as display_unit_song")
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('show_teater.setlist', 'like', "%{$search}%")
                    ->orWhere('show_teater.unit_song', 'like', "%{$search}%")
                    ->orWhere('setlist_cat.jp_name', 'like', "%{$search}%")
                    ->orWhere('unit_song_cat.jp_name', 'like', "%{$search}%");
            });
        }

        if ($setlistFilter) {
            $query->where('show_teater.setlist', $setlistFilter);
        }

        if ($dateFrom) {
            $query->whereDate('show_teater.show_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('show_teater.show_date', '<=', $dateTo);
        }

        $sortColumn = $sortBy === 'setlist' ? 'show_teater.setlist' : "show_teater.{$sortBy}";
        $shows = $query->orderBy($sortColumn, $sortDir)->paginate($perPage)->withQueryString();

        // Normalized unit songs (pivot) for the current page + fallback map.
        $unitSongByShow = $this->unitSongByShow($shows->getCollection()->pluck('show_id'));
        $unitSongJpMap = $this->unitSongJpMap();

        $shows->getCollection()->transform(function ($show) use ($unitSongByShow, $unitSongJpMap) {
            $show->display_unit_song = $this->formatUnitSongDisplay($show, $unitSongByShow, $unitSongJpMap);

            return $show;
        });

        $nextShowId = DB::table('show_teater')->max('show_id') + 1;

        // Get all unique setlists from categories for filter dropdown
        $allSetlists = ShowTeaterCategories::query()
            ->setlists()
            ->active()
            ->orderBy('name')
            ->pluck('name');

        // Get setlists with their unit songs for the create form
        $setlistsWithUnitSongs = ShowTeaterCategories::query()
            ->setlists()
            ->active()
            ->orderBy('name')
            ->with(['unitSongs' => fn ($query) => $query->active()->orderBy('name')])
            ->get(['id', 'name', 'jp_name'])
            ->map(function (ShowTeaterCategories $setlist): array {
                return [
                    'id' => $setlist->id,
                    'name' => $setlist->name,
                    'jp_name' => $setlist->jp_name,
                    'display_name' => $setlist->name.($setlist->jp_name ? ' ('.$setlist->jp_name.')' : ''),
                    'unit_songs' => $setlist->unitSongs->map(fn (ShowTeaterCategories $song): array => [
                        'id' => $song->id,
                        'name' => $song->name,
                        'jp_name' => $song->jp_name,
                        'display_name' => $song->name.($song->jp_name ? ' ('.$song->jp_name.')' : ''),
                    ]),
                ];
            });

        // Get last fetch timestamp
        $lastFetchAt = DB::table('show_teater')
            ->where('is_scraped_data', 1)
            ->whereNull('deleted_at')
            ->max('last_fetch_at');

        return view('show-teater.index', [
            'shows' => $shows,
            'nextShowId' => $nextShowId,
            'allSetlists' => $allSetlists,
            'setlistsWithUnitSongs' => $setlistsWithUnitSongs,
            'lastFetchAt' => $lastFetchAt,
            'filters' => [
                'search' => $search,
                'setlist' => $setlistFilter,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function export()
    {
        $shows = DB::table('show_teater')
            ->whereNull('deleted_at')
            ->orderBy('show_id')
            ->get();

        $unitSongByShow = $this->unitSongByShow($shows->pluck('show_id'));
        $unitSongJpMap = $this->unitSongJpMap();
        $setlistJpMap = $this->setlistJpMap();

        return Spreadsheet::download('show-teater-'.now()->format('Ymd-His').'.xlsx', [
            'Show ID',
            'Tanggal',
            'Setlist',
            'Unit Song',
            'Global Center',
            'US Center',
            'Event',
            'Info Tambahan',
        ], $shows->map(function ($show) use ($unitSongByShow, $unitSongJpMap, $setlistJpMap): array {
            $date = (string) $show->show_date;

            try {
                $date = Carbon::parse($show->show_date)->translatedFormat('d F Y');
            } catch (\Throwable $exception) {
                // Keep the original value if parsing fails.
            }

            return [
                $show->show_id,
                $date,
                $this->formatSetlistDisplay((string) $show->setlist, $setlistJpMap),
                $this->formatUnitSongDisplay($show, $unitSongByShow, $unitSongJpMap),
                $show->is_global_center ? 'Yes' : '-',
                $show->is_us_center ? 'Yes' : '-',
                $show->is_the_show_has_event ?: '-',
                $show->additional_information ?: '-',
            ];
        }));
    }

    /**
     * Pivot unit song per show (terurut), dikelompokkan per show_id.
     *
     * @param  Collection<int, int>  $showIds
     * @return Collection<int, Collection<int, object>>
     */
    private function unitSongByShow(Collection $showIds): Collection
    {
        return DB::table('show_teater_unit_song as pivot')
            ->join('show_teater_categories as category', 'pivot.show_teater_categories_id', '=', 'category.id')
            ->whereIn('pivot.show_id', $showIds)
            ->orderBy('pivot.position')
            ->get(['pivot.show_id', 'category.name', 'category.jp_name'])
            ->groupBy('show_id');
    }

    /**
     * Fallback: nama setlist => [nama unit song => jp_name].
     *
     * @return array<string, array<string, ?string>>
     */
    private function unitSongJpMap(): array
    {
        $map = [];

        $unitSongs = ShowTeaterCategories::query()
            ->unitSongs()
            ->with('setlist:id,name')
            ->get(['id', 'name', 'jp_name', 'setlist_id']);

        foreach ($unitSongs as $unitSong) {
            if ($unitSong->setlist) {
                $map[$unitSong->setlist->name][$unitSong->name] = $unitSong->jp_name;
            }
        }

        return $map;
    }

    /**
     * @return array<string, ?string>
     */
    private function setlistJpMap(): array
    {
        return ShowTeaterCategories::query()->setlists()->pluck('jp_name', 'name')->all();
    }

    /**
     * @param  Collection<int, Collection<int, object>>  $unitSongByShow
     * @param  array<string, array<string, ?string>>  $unitSongJpMap
     */
    private function formatUnitSongDisplay(object $show, Collection $unitSongByShow, array $unitSongJpMap): string
    {
        $normalized = $unitSongByShow->get($show->show_id);

        if ($normalized && $normalized->isNotEmpty()) {
            return $normalized
                ->map(fn ($song): string => $song->jp_name ? "{$song->name} ({$song->jp_name})" : (string) $song->name)
                ->implode('; ');
        }

        if (empty($show->unit_song)) {
            return '';
        }

        $formattedSongs = [];

        foreach (preg_split('/\s*;\s*/', $show->unit_song) ?: [] as $song) {
            if ($song === '') {
                continue;
            }

            $jpName = $unitSongJpMap[$show->setlist][$song] ?? null;
            $formattedSongs[] = $jpName ? "{$song} ({$jpName})" : $song;
        }

        return implode('; ', $formattedSongs);
    }

    /**
     * @param  array<string, ?string>  $setlistJpMap
     */
    private function formatSetlistDisplay(string $setlist, array $setlistJpMap): string
    {
        $jpName = $setlistJpMap[$setlist] ?? null;

        return $jpName ? "{$setlist} ({$jpName})" : $setlist;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'show_id' => 'required|integer',
            'show_date' => 'required|string|max:25',
            'setlist' => 'required|string|max:32',
            'unit_song' => 'nullable|string|max:100',
            'unit_song_2' => 'nullable|string|max:100',
            'is_global_center' => 'nullable|boolean',
            'is_us_center' => 'nullable|boolean',
            'is_the_show_has_event' => 'nullable|string|max:56',
            'additional_information' => 'nullable|string|max:56',
        ]);

        $unitSong = $validated['unit_song'] ?? '';
        if ($request->has('double_us') && $request->filled('unit_song_2')) {
            $unitSong = ($validated['unit_song'] ?? '').'; '.$request->input('unit_song_2');
        }

        // Convert date from YYYY-MM-DD (HTML input) to YYYY/MM/DD (DB format)
        $showDate = str_replace('-', '/', $validated['show_date']);

        $existing = DB::table('show_teater')->where('show_id', $validated['show_id'])->first();

        if ($existing && $existing->deleted_at === null) {
            return back()->withErrors(['show_id' => 'Show ID sudah digunakan.'])->withInput();
        }

        $payload = [
            'show_date' => $showDate,
            'setlist' => $validated['setlist'],
            'unit_song' => substr($unitSong, 0, 100),
            'is_global_center' => $validated['is_global_center'] ?? 0,
            'is_us_center' => $validated['is_us_center'] ?? 0,
            'is_the_show_has_event' => $validated['is_the_show_has_event'] ?? null,
            'additional_information' => $validated['additional_information'] ?? null,
            'is_member_show' => 1,
        ];

        if ($existing) {
            // Baris yang sudah ter-soft delete: pulihkan dengan show_id yang sama.
            DB::table('show_teater')
                ->where('show_id', $validated['show_id'])
                ->update($payload + ['deleted_at' => null]);
        } else {
            DB::table('show_teater')->insert(['show_id' => $validated['show_id']] + $payload);
        }

        app(ShowTeaterNormalizer::class)->syncShow((int) $validated['show_id']);

        Cache::flush();

        return redirect()->route('show-teater.index')->with('success', 'Show berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'show_date' => 'required|string|max:25',
            'setlist' => 'required|string|max:32',
            'unit_song' => 'nullable|string|max:100',
            'unit_song_2' => 'nullable|string|max:100',
            'is_global_center' => 'nullable|boolean',
            'is_us_center' => 'nullable|boolean',
            'is_the_show_has_event' => 'nullable|string|max:56',
            'additional_information' => 'nullable|string|max:56',
        ]);

        $unitSong = $validated['unit_song'] ?? '';
        if ($request->has('double_us') && $request->filled('unit_song_2')) {
            $unitSong = ($validated['unit_song'] ?? '').'; '.$request->input('unit_song_2');
        }

        // Convert date from YYYY-MM-DD (HTML input) to YYYY/MM/DD (DB format)
        $showDate = str_replace('-', '/', $validated['show_date']);

        DB::table('show_teater')
            ->where('show_id', $id)
            ->whereNull('deleted_at')
            ->update([
                'show_date' => $showDate,
                'setlist' => $validated['setlist'],
                'unit_song' => substr($unitSong, 0, 100),
                'is_global_center' => $validated['is_global_center'] ?? 0,
                'is_us_center' => $validated['is_us_center'] ?? 0,
                'is_the_show_has_event' => $validated['is_the_show_has_event'] ?? null,
                'additional_information' => $validated['additional_information'] ?? null,
            ]);

        app(ShowTeaterNormalizer::class)->syncShow((int) $id);

        Cache::flush();

        return redirect()->route('show-teater.index')->with('success', 'Show berhasil diupdate.');
    }

    public function confirmMemberShow(Request $request, $id)
    {
        $show = DB::table('show_teater')
            ->where('show_id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (! $show) {
            return response()->json(['error' => 'Show not found'], 404);
        }

        DB::table('show_teater')
            ->where('show_id', $id)
            ->whereNull('deleted_at')
            ->update(['is_member_show' => 1]);

        Cache::flush();

        return response()->json(['success' => true, 'message' => 'Show confirmed as member show']);
    }

    public function rejectMemberShow(Request $request, $id)
    {
        $show = DB::table('show_teater')
            ->where('show_id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (! $show) {
            return response()->json(['error' => 'Show not found'], 404);
        }

        DB::table('show_teater')
            ->where('show_id', $id)
            ->update(['deleted_at' => now()]);

        Cache::flush();

        return response()->json(['success' => true, 'message' => 'Show has been deleted']);
    }

    public function fetchManually(Request $request)
    {
        try {
            $exitCode = Artisan::call('app:fetch-theater-shows');
            $output = Artisan::output();

            if ($exitCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => trim($output) ?: 'Gagal mengambil data dari API JKT48.',
                ], 500);
            }

            // Update last_fetch_at for scraped data
            DB::table('show_teater')
                ->where('is_scraped_data', 1)
                ->whereNull('deleted_at')
                ->update(['last_fetch_at' => now()]);

            Cache::flush();

            $message = trim($output);

            if ($message === '') {
                $message = 'Data berhasil di-fetch!';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: '.$e->getMessage(),
            ], 500);
        }
    }
}
