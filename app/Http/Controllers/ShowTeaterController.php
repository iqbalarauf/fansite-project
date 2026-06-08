<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use App\Models\ShowTeater;

class ShowTeaterController extends Controller
{
    public function index()
    {
        // Get all shows without pagination for client-side filtering
        $shows = DB::table('show_teater')
            ->leftJoin('show_teater_categories as setlist_cat', function($join) {
                $join->on('show_teater.setlist', '=', 'setlist_cat.name')
                     ->where('setlist_cat.type', '=', 'setlist');
            })
            ->leftJoin('show_teater_categories as unit_song_cat', function($join) {
                $join->on('show_teater.unit_song', '=', 'unit_song_cat.name')
                     ->where('unit_song_cat.type', '=', 'unit_song');
            })
            ->select('show_teater.*',
                     'setlist_cat.jp_name as setlist_jp_name',
                     'unit_song_cat.jp_name as unit_song_jp_name',
                     DB::raw("CONCAT(show_teater.setlist, IF(setlist_cat.jp_name IS NOT NULL AND setlist_cat.jp_name != '', CONCAT(' (', setlist_cat.jp_name, ')'), '')) as display_setlist"),
                     DB::raw("CONCAT(show_teater.unit_song, IF(unit_song_cat.jp_name IS NOT NULL AND unit_song_cat.jp_name != '', CONCAT(' (', unit_song_cat.jp_name, ')'), '')) as display_unit_song"))
            ->orderBy('show_teater.show_id', 'desc')
            ->get()
            ->unique('show_id')
            ->values();

        $nextShowId = DB::table('show_teater')->max('show_id') + 1;

        // Get all unique setlists from categories
        $allSetlists = DB::table('show_teater_categories')
            ->where('type', 'setlist')
            ->orderBy('name')
            ->pluck('name');

        // Get setlists with their unit songs for dropdown
        $setlistsWithUnitSongs = DB::table('show_teater_categories as setlists')
            ->select('setlists.id', 'setlists.name', 'setlists.jp_name')
            ->where('setlists.type', 'setlist')
            ->orderBy('setlists.name')
            ->get()
            ->map(function ($setlist) {
                $unitSongs = DB::table('show_teater_categories')
                    ->where('type', 'unit_song')
                    ->where('setlist_id', $setlist->id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'jp_name']);

                return [
                    'id' => $setlist->id,
                    'name' => $setlist->name,
                    'jp_name' => $setlist->jp_name,
                    'display_name' => $setlist->name . ($setlist->jp_name ? ' (' . $setlist->jp_name . ')' : ''),
                    'unit_songs' => $unitSongs->map(function ($song) {
                        return [
                            'id' => $song->id,
                            'name' => $song->name,
                            'jp_name' => $song->jp_name,
                            'display_name' => $song->name . ($song->jp_name ? ' (' . $song->jp_name . ')' : ''),
                        ];
                    }),
                ];
            });

        // Get last fetch timestamp
        $lastFetchAt = DB::table('show_teater')
            ->where('is_scraped_data', 1)
            ->max('last_fetch_at');

        return Inertia::render('ShowTeater/Index', [
            'shows' => $shows,
            'nextShowId' => $nextShowId,
            'allSetlists' => $allSetlists,
            'setlistsWithUnitSongs' => $setlistsWithUnitSongs,
            'lastFetchAt' => $lastFetchAt,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'show_id' => 'required|integer|unique:show_teater,show_id',
            'show_date' => 'required|string|max:25',
            'setlist' => 'required|string|max:32',
            'unit_song' => 'required|string|max:100',
            'is_global_center' => 'nullable|boolean',
            'is_us_center' => 'nullable|boolean',
            'is_the_show_has_event' => 'nullable|string|max:56',
            'additional_information' => 'nullable|string|max:56',
        ]);

        DB::table('show_teater')->insert([
            'show_id' => $validated['show_id'],
            'show_date' => $validated['show_date'],
            'setlist' => $validated['setlist'],
            'unit_song' => $validated['unit_song'],
            'is_global_center' => $validated['is_global_center'] ?? 0,
            'is_us_center' => $validated['is_us_center'] ?? 0,
            'is_the_show_has_event' => $validated['is_the_show_has_event'] ?? null,
            'additional_information' => $validated['additional_information'] ?? null,
        ]);

        return redirect()->route('show-teater.index')->with('success', 'Show berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'show_date' => 'required|string|max:25',
            'setlist' => 'required|string|max:32',
            'unit_song' => 'required|string|max:100',
            'is_global_center' => 'nullable|boolean',
            'is_us_center' => 'nullable|boolean',
            'is_the_show_has_event' => 'nullable|string|max:56',
            'additional_information' => 'nullable|string|max:56',
        ]);

        DB::table('show_teater')
            ->where('show_id', $id)
            ->update([
                'show_date' => $validated['show_date'],
                'setlist' => $validated['setlist'],
                'unit_song' => $validated['unit_song'],
                'is_global_center' => $validated['is_global_center'] ?? 0,
                'is_us_center' => $validated['is_us_center'] ?? 0,
                'is_the_show_has_event' => $validated['is_the_show_has_event'] ?? null,
                'additional_information' => $validated['additional_information'] ?? null,
            ]);

        return redirect()->route('show-teater.index')->with('success', 'Show berhasil diupdate');
    }

    public function confirmMemberShow(Request $request, $id)
    {
        $show = DB::table('show_teater')
            ->where('show_id', $id)
            ->first();

        if (!$show) {
            return response()->json(['error' => 'Show not found'], 404);
        }

        DB::table('show_teater')
            ->where('show_id', $id)
            ->update(['is_member_show' => 1]);

        return response()->json(['success' => true, 'message' => 'Show confirmed as member show']);
    }

    public function rejectMemberShow(Request $request, $id)
    {
        $show = DB::table('show_teater')
            ->where('show_id', $id)
            ->first();

        if (!$show) {
            return response()->json(['error' => 'Show not found'], 404);
        }

        DB::table('show_teater')
            ->where('show_id', $id)
            ->delete();

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

            return response()->json([
                'success' => true,
                'message' => 'Data fetched successfully',
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage()
            ], 500);
        }
    }
}
