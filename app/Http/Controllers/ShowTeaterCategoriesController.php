<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShowTeaterCategoriesController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'setlist');
        $search = $request->get('search');
        $setlistFilter = $request->get('setlist');
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $perPage = (int) $request->get('per_page', 10);

        $allowedSorts = ['id', 'name', 'jp_name'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        // --- Setlist table ---
        $setlistQuery = DB::table('show_teater_categories as c')
            ->where('c.type', 'setlist')
            ->select(
                'c.id',
                'c.name',
                'c.jp_name',
                'c.status as is_active',
                'c.created_at',
                DB::raw("CONCAT(c.name, IF(c.jp_name IS NOT NULL AND c.jp_name != '', CONCAT(' (', c.jp_name, ')'), '')) as display_name")
            );

        if ($search && $tab === 'setlist') {
            $setlistQuery->where(function ($q) use ($search) {
                $q->where('c.name', 'like', "%{$search}%")
                    ->orWhere('c.jp_name', 'like', "%{$search}%");
            });
        }

        $setlists = $setlistQuery->orderBy("c.{$sortBy}", $sortDir)->paginate($perPage, ['*'], 'setlist_page')->withQueryString();

        // --- Unit Song table ---
        $unitSongQuery = DB::table('show_teater_categories as c')
            ->leftJoin('show_teater_categories as s', 'c.setlist_id', '=', 's.id')
            ->where('c.type', 'unit_song')
            ->select(
                'c.id',
                'c.name',
                'c.jp_name',
                'c.setlist_id',
                'c.status as is_active',
                'c.created_at',
                's.name as setlist_name',
                's.jp_name as setlist_jp_name',
                DB::raw("CONCAT(c.name, IF(c.jp_name IS NOT NULL AND c.jp_name != '', CONCAT(' (', c.jp_name, ')'), '')) as display_name"),
                DB::raw("CONCAT(s.name, IF(s.jp_name IS NOT NULL AND s.jp_name != '', CONCAT(' (', s.jp_name, ')'), '')) as display_setlist_name")
            );

        if ($search && $tab === 'unit_song') {
            $unitSongQuery->where(function ($q) use ($search) {
                $q->where('c.name', 'like', "%{$search}%")
                    ->orWhere('c.jp_name', 'like', "%{$search}%")
                    ->orWhere('s.name', 'like', "%{$search}%");
            });
        }

        if ($setlistFilter) {
            $unitSongQuery->where('c.setlist_id', $setlistFilter);
        }

        $unitSongs = $unitSongQuery->orderBy("c.{$sortBy}", $sortDir)->paginate($perPage, ['*'], 'unit_page')->withQueryString();

        // All active setlists for dropdowns
        $allSetlists = DB::table('show_teater_categories')
            ->where('type', 'setlist')
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'jp_name']);

        // All setlists (including inactive) for filter dropdown
        $allSetlistsForFilter = DB::table('show_teater_categories')
            ->where('type', 'setlist')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('show-teater.categories', [
            'setlists' => $setlists,
            'unitSongs' => $unitSongs,
            'allSetlists' => $allSetlists,
            'allSetlistsForFilter' => $allSetlistsForFilter,
            'tab' => $tab,
            'filters' => [
                'search' => $search,
                'setlist' => $setlistFilter,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
                'tab' => $tab,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:setlist,unit_song',
            'name' => 'required|string|max:100',
            'jp_name' => 'nullable|string|max:100',
            'setlist_id' => 'required_if:type,unit_song|nullable|exists:show_teater_categories,id',
        ]);

        $query = DB::table('show_teater_categories')
            ->where('type', $validated['type'])
            ->where('name', $validated['name']);

        if ($validated['type'] === 'unit_song' && ! empty($validated['setlist_id'])) {
            $query->where('setlist_id', $validated['setlist_id']);
        }

        if ($query->exists()) {
            return back()->withErrors(['name' => 'Kategori sudah ada.'])->withInput();
        }

        DB::table('show_teater_categories')->insert([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'jp_name' => $validated['jp_name'] ?? null,
            'setlist_id' => $validated['type'] === 'unit_song' ? ($validated['setlist_id'] ?? null) : null,
            'status' => 1,
            'created_at' => now(),
        ]);

        return redirect()
            ->route('show-teater.categories.index', ['tab' => $validated['type'] === 'setlist' ? 'setlist' : 'unit_song'])
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'jp_name' => 'nullable|string|max:100',
            'setlist_id' => 'nullable|exists:show_teater_categories,id',
        ]);

        $current = DB::table('show_teater_categories')->where('id', $id)->first();

        if (! $current) {
            return back()->withErrors(['id' => 'Data tidak ditemukan.']);
        }

        $query = DB::table('show_teater_categories')
            ->where('type', $current->type)
            ->where('name', $validated['name'])
            ->where('id', '!=', $id);

        if ($current->type === 'unit_song' && ! empty($validated['setlist_id'])) {
            $query->where('setlist_id', $validated['setlist_id']);
        }

        if ($query->exists()) {
            return back()->withErrors(['name' => 'Kategori sudah ada.'])->withInput();
        }

        DB::table('show_teater_categories')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'jp_name' => $validated['jp_name'] ?? null,
                'setlist_id' => $current->type === 'unit_song' ? ($validated['setlist_id'] ?? $current->setlist_id) : null,
            ]);

        return redirect()
            ->route('show-teater.categories.index', ['tab' => $current->type === 'setlist' ? 'setlist' : 'unit_song'])
            ->with('success', 'Kategori berhasil diupdate.');
    }

    public function toggleStatus($id)
    {
        $category = DB::table('show_teater_categories')->where('id', $id)->first();

        if (! $category) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $newStatus = $category->status ? 0 : 1;

        DB::table('show_teater_categories')->where('id', $id)->update(['status' => $newStatus]);

        // If deactivating a setlist, also deactivate its unit songs
        if ($category->type === 'setlist' && $newStatus === 0) {
            DB::table('show_teater_categories')
                ->where('type', 'unit_song')
                ->where('setlist_id', $id)
                ->update(['status' => 0]);
        }

        return response()->json(['success' => true, 'is_active' => $newStatus]);
    }
}
