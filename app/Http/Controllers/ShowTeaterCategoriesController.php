<?php

namespace App\Http\Controllers;

use App\Models\ShowTeaterCategories;
use App\Support\Spreadsheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        $setlists = ShowTeaterCategories::query()
            ->setlists()
            ->when($search && $tab === 'setlist', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('jp_name', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage, ['*'], 'setlist_page')
            ->withQueryString();

        // --- Unit Song table ---
        $unitSongs = ShowTeaterCategories::query()
            ->from('show_teater_categories as c')
            ->leftJoin('show_teater_categories as s', 'c.setlist_id', '=', 's.id')
            ->where('c.type', 'unit_song')
            ->select('c.*', 's.name as setlist_name', 's.jp_name as setlist_jp_name')
            ->when($search && $tab === 'unit_song', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('c.name', 'like', "%{$search}%")
                        ->orWhere('c.jp_name', 'like', "%{$search}%")
                        ->orWhere('s.name', 'like', "%{$search}%");
                });
            })
            ->when($setlistFilter, fn ($query) => $query->where('c.setlist_id', $setlistFilter))
            ->orderBy("c.{$sortBy}", $sortDir)
            ->paginate($perPage, ['*'], 'unit_page')
            ->withQueryString();

        // All active setlists for dropdowns.
        $allSetlists = ShowTeaterCategories::query()
            ->setlists()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'jp_name']);

        // All setlists (including inactive) for the filter dropdown.
        $allSetlistsForFilter = ShowTeaterCategories::query()
            ->setlists()
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

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:xlsx,csv,txt', 'max:4096'],
        ], [], ['file' => 'File']);

        $rows = Spreadsheet::rows($request->file('file'));

        if ($rows === []) {
            return back()->withErrors(['file' => 'File kosong atau tidak dapat dibaca.']);
        }

        $header = array_map(
            static fn ($value): string => strtolower(trim((string) $value)),
            array_shift($rows),
        );

        $added = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $record = [];

            foreach ($header as $position => $key) {
                $record[$key] = trim((string) ($row[$position] ?? ''));
            }

            $type = strtolower($record['type'] ?? '');
            $name = $record['name'] ?? '';
            $jpName = $record['jp_name'] ?? '';
            $setlistName = $record['setlist'] ?? '';
            $line = $index + 2;

            if (! in_array($type, ['setlist', 'unit_song'], true) || $name === '') {
                $errors[] = "Baris {$line}: kolom type/name tidak valid.";

                continue;
            }

            if ($type === 'setlist') {
                $setlist = ShowTeaterCategories::query()->setlists()->where('name', $name)->first();

                if ($setlist) {
                    $setlist->update(['jp_name' => $jpName !== '' ? $jpName : null]);
                    $updated++;
                } else {
                    ShowTeaterCategories::query()->create([
                        'type' => ShowTeaterCategories::TYPE_SETLIST,
                        'name' => $name,
                        'jp_name' => $jpName !== '' ? $jpName : null,
                        'setlist_id' => null,
                        'is_active' => true,
                    ]);
                    $added++;
                }

                continue;
            }

            if ($setlistName === '') {
                $errors[] = "Baris {$line}: unit_song membutuhkan kolom setlist.";

                continue;
            }

            $setlist = ShowTeaterCategories::query()->setlists()->where('name', $setlistName)->first();

            if (! $setlist) {
                $errors[] = "Baris {$line}: setlist \"{$setlistName}\" tidak ditemukan.";

                continue;
            }

            $existing = ShowTeaterCategories::query()
                ->unitSongs()
                ->where('setlist_id', $setlist->id)
                ->where('name', $name)
                ->first();

            if ($existing) {
                $existing->update(['jp_name' => $jpName !== '' ? $jpName : null]);
                $updated++;
            } else {
                ShowTeaterCategories::query()->create([
                    'type' => ShowTeaterCategories::TYPE_UNIT_SONG,
                    'name' => $name,
                    'jp_name' => $jpName !== '' ? $jpName : null,
                    'setlist_id' => $setlist->id,
                    'is_active' => true,
                ]);
                $added++;
            }
        }

        $message = "Import selesai: {$added} ditambahkan, {$updated} diperbarui.";
        $redirect = redirect()->route('show-teater.categories.index')->with('success', $message);

        if ($errors !== []) {
            return $redirect->withErrors(['file' => $errors]);
        }

        return $redirect;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:setlist,unit_song',
            'name' => 'required|string|max:100',
            'jp_name' => 'nullable|string|max:100',
            'setlist_id' => 'required_if:type,unit_song|nullable|exists:show_teater_categories,id',
        ]);

        $query = ShowTeaterCategories::query()
            ->where('type', $validated['type'])
            ->where('name', $validated['name']);

        if ($validated['type'] === ShowTeaterCategories::TYPE_UNIT_SONG && ! empty($validated['setlist_id'])) {
            $query->where('setlist_id', $validated['setlist_id']);
        }

        if ($query->exists()) {
            return back()->withErrors(['name' => 'Kategori sudah ada.'])->withInput();
        }

        ShowTeaterCategories::query()->create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'jp_name' => $validated['jp_name'] ?? null,
            'setlist_id' => $validated['type'] === ShowTeaterCategories::TYPE_UNIT_SONG ? ($validated['setlist_id'] ?? null) : null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('show-teater.categories.index', ['tab' => $validated['type'] === ShowTeaterCategories::TYPE_SETLIST ? 'setlist' : 'unit_song'])
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'jp_name' => 'nullable|string|max:100',
            'setlist_id' => 'nullable|exists:show_teater_categories,id',
        ]);

        $current = ShowTeaterCategories::query()->find($id);

        if (! $current) {
            return back()->withErrors(['id' => 'Data tidak ditemukan.']);
        }

        $query = ShowTeaterCategories::query()
            ->where('type', $current->type)
            ->where('name', $validated['name'])
            ->whereKeyNot($id);

        if ($current->type === ShowTeaterCategories::TYPE_UNIT_SONG && ! empty($validated['setlist_id'])) {
            $query->where('setlist_id', $validated['setlist_id']);
        }

        if ($query->exists()) {
            return back()->withErrors(['name' => 'Kategori sudah ada.'])->withInput();
        }

        $current->update([
            'name' => $validated['name'],
            'jp_name' => $validated['jp_name'] ?? null,
            'setlist_id' => $current->type === ShowTeaterCategories::TYPE_UNIT_SONG ? ($validated['setlist_id'] ?? $current->setlist_id) : null,
        ]);

        return redirect()
            ->route('show-teater.categories.index', ['tab' => $current->type === ShowTeaterCategories::TYPE_SETLIST ? 'setlist' : 'unit_song'])
            ->with('success', 'Kategori berhasil diupdate.');
    }

    public function toggleStatus($id)
    {
        $category = ShowTeaterCategories::query()->find($id);

        if (! $category) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $newStatus = ! $category->is_active;

        $category->update(['is_active' => $newStatus]);

        // Menonaktifkan setlist juga menonaktifkan unit song di dalamnya.
        if ($category->type === ShowTeaterCategories::TYPE_SETLIST && ! $newStatus) {
            $category->unitSongs()->update(['is_active' => false]);
        }

        return response()->json(['success' => true, 'is_active' => $newStatus ? 1 : 0]);
    }
}
