<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        // Build a mapping of setlist_name -> [unit_song_name => jp_name] for formatting multiple unit songs
        $unitSongsList = DB::table('show_teater_categories as unit_songs')
            ->join('show_teater_categories as setlists', 'unit_songs.setlist_id', '=', 'setlists.id')
            ->where('unit_songs.type', 'unit_song')
            ->where('setlists.type', 'setlist')
            ->get(['unit_songs.name as song_name', 'unit_songs.jp_name as song_jp_name', 'setlists.name as setlist_name']);

        $unitSongJpMap = [];
        foreach ($unitSongsList as $item) {
            $unitSongJpMap[$item->setlist_name][$item->song_name] = $item->song_jp_name;
        }

        $shows->getCollection()->transform(function ($show) use ($unitSongJpMap) {
            if (empty($show->unit_song)) {
                $show->display_unit_song = '';
                return $show;
            }

            $songs = explode(', ', $show->unit_song);
            $formattedSongs = [];
            foreach ($songs as $song) {
                $jpName = $unitSongJpMap[$show->setlist][$song] ?? null;
                if ($jpName) {
                    $formattedSongs[] = "{$song} ({$jpName})";
                } else {
                    $formattedSongs[] = $song;
                }
            }

            $show->display_unit_song = implode(', ', $formattedSongs);
            return $show;
        });

        $nextShowId = DB::table('show_teater')->max('show_id') + 1;

        // Get all unique setlists from categories for filter dropdown
        $allSetlists = DB::table('show_teater_categories')
            ->where('type', 'setlist')
            ->where('status', 1)
            ->orderBy('name')
            ->pluck('name');

        // Get setlists with their unit songs for the create form
        $setlistsWithUnitSongs = DB::table('show_teater_categories as setlists')
            ->select('setlists.id', 'setlists.name', 'setlists.jp_name')
            ->where('setlists.type', 'setlist')
            ->where('setlists.status', 1)
            ->orderBy('setlists.name')
            ->get()
            ->map(function ($setlist) {
                $unitSongs = DB::table('show_teater_categories')
                    ->where('type', 'unit_song')
                    ->where('setlist_id', $setlist->id)
                    ->where('status', 1)
                    ->orderBy('name')
                    ->get(['id', 'name', 'jp_name']);

                return [
                    'id' => $setlist->id,
                    'name' => $setlist->name,
                    'jp_name' => $setlist->jp_name,
                    'display_name' => $setlist->name.($setlist->jp_name ? ' ('.$setlist->jp_name.')' : ''),
                    'unit_songs' => $unitSongs->map(function ($song) {
                        return [
                            'id' => $song->id,
                            'name' => $song->name,
                            'jp_name' => $song->jp_name,
                            'display_name' => $song->name.($song->jp_name ? ' ('.$song->jp_name.')' : ''),
                        ];
                    }),
                ];
            });

        // Get last fetch timestamp
        $lastFetchAt = DB::table('show_teater')
            ->where('is_scraped_data', 1)
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'show_id' => 'required|integer|unique:show_teater,show_id',
            'show_date' => 'required|string|max:25',
            'setlist' => 'required|string|max:32',
            'unit_song' => 'required|string|max:100',
            'unit_song_2' => 'nullable|string|max:100',
            'is_global_center' => 'nullable|boolean',
            'is_us_center' => 'nullable|boolean',
            'is_the_show_has_event' => 'nullable|string|max:56',
            'additional_information' => 'nullable|string|max:56',
        ]);

        $unitSong = $validated['unit_song'];
        if ($request->has('double_us') && $request->filled('unit_song_2')) {
            $unitSong = $validated['unit_song'].', '.$request->input('unit_song_2');
        }

        DB::table('show_teater')->insert([
            'show_id' => $validated['show_id'],
            'show_date' => $validated['show_date'],
            'setlist' => $validated['setlist'],
            'unit_song' => substr($unitSong, 0, 100),
            'is_global_center' => $validated['is_global_center'] ?? 0,
            'is_us_center' => $validated['is_us_center'] ?? 0,
            'is_the_show_has_event' => $validated['is_the_show_has_event'] ?? null,
            'additional_information' => $validated['additional_information'] ?? null,
            'is_member_show' => 1,
        ]);

        Cache::flush();

        return redirect()->route('show-teater.index')->with('success', 'Show berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'show_date' => 'required|string|max:25',
            'setlist' => 'required|string|max:32',
            'unit_song' => 'required|string|max:100',
            'unit_song_2' => 'nullable|string|max:100',
            'is_global_center' => 'nullable|boolean',
            'is_us_center' => 'nullable|boolean',
            'is_the_show_has_event' => 'nullable|string|max:56',
            'additional_information' => 'nullable|string|max:56',
        ]);

        $unitSong = $validated['unit_song'];
        if ($request->has('double_us') && $request->filled('unit_song_2')) {
            $unitSong = $validated['unit_song'].', '.$request->input('unit_song_2');
        }

        DB::table('show_teater')
            ->where('show_id', $id)
            ->update([
                'show_date' => $validated['show_date'],
                'setlist' => $validated['setlist'],
                'unit_song' => substr($unitSong, 0, 100),
                'is_global_center' => $validated['is_global_center'] ?? 0,
                'is_us_center' => $validated['is_us_center'] ?? 0,
                'is_the_show_has_event' => $validated['is_the_show_has_event'] ?? null,
                'additional_information' => $validated['additional_information'] ?? null,
            ]);

        Cache::flush();

        return redirect()->route('show-teater.index')->with('success', 'Show berhasil diupdate.');
    }

    public function confirmMemberShow(Request $request, $id)
    {
        $show = DB::table('show_teater')
            ->where('show_id', $id)
            ->first();

        if (! $show) {
            return response()->json(['error' => 'Show not found'], 404);
        }

        DB::table('show_teater')
            ->where('show_id', $id)
            ->update(['is_member_show' => 1]);

        Cache::flush();

        return response()->json(['success' => true, 'message' => 'Show confirmed as member show']);
    }

    public function rejectMemberShow(Request $request, $id)
    {
        $show = DB::table('show_teater')
            ->where('show_id', $id)
            ->first();

        if (! $show) {
            return response()->json(['error' => 'Show not found'], 404);
        }

        DB::table('show_teater')
            ->where('show_id', $id)
            ->delete();

        Cache::flush();

        return response()->json(['success' => true, 'message' => 'Show has been deleted']);
    }

    public function fetchManually(Request $request)
    {
        try {
            Artisan::call('app:fetch-theater-shows');

            // Update last_fetch_at for scraped data
            DB::table('show_teater')
                ->where('is_scraped_data', 1)
                ->update(['last_fetch_at' => now()]);

            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Data fetched successfully',
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
