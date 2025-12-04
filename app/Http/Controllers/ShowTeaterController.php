<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ShowTeaterController extends Controller
{
    public function index()
    {
        $shows = DB::table('show_teater')
            ->orderBy('show_id', 'desc')
            ->paginate(10);

        $nextShowId = DB::table('show_teater')->max('show_id') + 1;

        return Inertia::render('ShowTeater/Index', [
            'shows' => $shows,
            'nextShowId' => $nextShowId,
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
