<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ShowTeaterCategoryController extends Controller
{
    public function index()
    {
        $categories = DB::table('show_teater_categories')
            ->leftJoin('show_teater_categories as setlists', function($join) {
                $join->on('show_teater_categories.setlist_id', '=', 'setlists.id')
                     ->where('setlists.type', '=', 'setlist');
            })
            ->select('show_teater_categories.*', 'setlists.name as setlist_name')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        // Get all setlists for dropdown
        $setlists = DB::table('show_teater_categories')
            ->where('type', 'setlist')
            ->orderBy('name')
            ->get();

        return Inertia::render('ShowTeater/Category', [
            'categories' => $categories,
            'setlists' => $setlists,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:setlist,unit_song',
            'name' => 'required|string|max:100',
            'setlist_id' => 'required_if:type,unit_song|nullable|exists:show_teater_categories,id',
        ]);

        // Check if already exists
        $query = DB::table('show_teater_categories')
            ->where('type', $validated['type'])
            ->where('name', $validated['name']);

        if ($validated['type'] === 'unit_song' && isset($validated['setlist_id'])) {
            $query->where('setlist_id', $validated['setlist_id']);
        }

        if ($query->exists()) {
            return back()->withErrors(['name' => 'Category already exists']);
        }

        DB::table('show_teater_categories')->insert([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'setlist_id' => $validated['type'] === 'unit_song' ? $validated['setlist_id'] : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('show-teater.categories.index')->with('success', 'Category added successfully');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:setlist,unit_song',
            'name' => 'required|string|max:100',
            'setlist_id' => 'required_if:type,unit_song|nullable|exists:show_teater_categories,id',
        ]);

        // Check if already exists (excluding current record)
        $query = DB::table('show_teater_categories')
            ->where('type', $validated['type'])
            ->where('name', $validated['name'])
            ->where('id', '!=', $id);

        if ($validated['type'] === 'unit_song' && isset($validated['setlist_id'])) {
            $query->where('setlist_id', $validated['setlist_id']);
        }

        if ($query->exists()) {
            return back()->withErrors(['name' => 'Category already exists']);
        }

        DB::table('show_teater_categories')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'setlist_id' => $validated['type'] === 'unit_song' ? $validated['setlist_id'] : null,
                'updated_at' => now(),
            ]);

        return redirect()->route('show-teater.categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        DB::table('show_teater_categories')->where('id', $id)->delete();

        return redirect()->route('show-teater.categories.index')->with('success', 'Category deleted successfully');
    }
}
