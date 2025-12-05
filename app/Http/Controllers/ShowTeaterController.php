<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ShowTeaterController extends Controller
{
    public function index()
    {
        // Get all shows without pagination for client-side filtering
        $shows = DB::table('show_teater')
            ->orderBy('show_id', 'desc')
            ->get();

        $nextShowId = DB::table('show_teater')->max('show_id') + 1;

        // Get all unique setlists from categories
        $allSetlists = DB::table('show_teater_categories')
            ->where('type', 'setlist')
            ->orderBy('name')
            ->pluck('name');

        // Get setlists with their unit songs for dropdown
        $setlistsWithUnitSongs = DB::table('show_teater_categories as setlists')
            ->select('setlists.id', 'setlists.name')
            ->where('setlists.type', 'setlist')
            ->orderBy('setlists.name')
            ->get()
            ->map(function ($setlist) {
                $unitSongs = DB::table('show_teater_categories')
                    ->where('type', 'unit_song')
                    ->where('setlist_id', $setlist->id)
                    ->orderBy('name')
                    ->get(['id', 'name']);

                return [
                    'id' => $setlist->id,
                    'name' => $setlist->name,
                    'unit_songs' => $unitSongs,
                ];
            });

        return Inertia::render('ShowTeater/Index', [
            'shows' => $shows,
            'nextShowId' => $nextShowId,
            'allSetlists' => $allSetlists,
            'setlistsWithUnitSongs' => $setlistsWithUnitSongs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'show_id' => 'required|integer|unique:show_teater,show_id',
            'show_date' => 'required|string|max:25',
            'setlist' => 'required|string|max:32',
            'unit_song' => 'required|string|max:29',
            'is_global_center' => 'nullable|boolean',
            'is_us_center' => 'nullable|boolean',
            'is_the_show_has_event' => 'nullable|string|max:41',
            'additional_information' => 'nullable|string|max:48',
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
            'unit_song' => 'required|string|max:29',
            'is_global_center' => 'nullable|boolean',
            'is_us_center' => 'nullable|boolean',
            'is_the_show_has_event' => 'nullable|string|max:41',
            'additional_information' => 'nullable|string|max:48',
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
}
