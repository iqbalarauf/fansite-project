<?php

namespace App\Http\Controllers;

use App\Http\Requests\MagazineStoreRequest;
use App\Http\Requests\MagazineUpdateRequest;
use App\Models\Magazine;
use App\Support\ImageOptimizer;
use App\Support\ListingQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MagazineController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListingQuery::from($request, ['title', 'views', 'downloads', 'created_at'], 'created_at');

        $magazines = Magazine::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nestedQuery) use ($filters): void {
                    $nestedQuery->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('slug', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('magazines.index', [
            'magazines' => $magazines,
            'filters' => $filters,
        ]);
    }

    public function store(MagazineStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $isMain = $request->boolean('is_main');

        $magazine = DB::transaction(function () use ($isMain, $validated): Magazine {
            if ($isMain) {
                Magazine::query()->where('is_main', true)->update(['is_main' => false]);
            }

            return Magazine::query()->create([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'cover' => isset($validated['cover']) ? ImageOptimizer::store($validated['cover'], 'magazines/covers', maxWidth: 1000) : null,
                'file_path' => $validated['file']->store('magazines/files', 'public'),
                'original_name' => $validated['file']->getClientOriginalName(),
                'is_main' => $isMain,
            ]);
        });

        return redirect()->route('magazines.index')
            ->with('success', "Majalah \"{$magazine->title}\" berhasil ditambahkan.");
    }

    public function update(MagazineUpdateRequest $request, Magazine $magazine): RedirectResponse
    {
        $magazine->update($request->magazinePayload());

        return redirect()->route('magazines.index')
            ->with('success', 'Majalah berhasil diupdate.');
    }

    public function setMain(Magazine $magazine): RedirectResponse
    {
        DB::transaction(function () use ($magazine): void {
            Magazine::query()->where('is_main', true)->update(['is_main' => false]);
            $magazine->update(['is_main' => true]);
        });

        return redirect()->route('magazines.index')
            ->with('success', "Majalah \"{$magazine->title}\" dijadikan main magazine.");
    }

    public function destroy(Magazine $magazine): RedirectResponse
    {
        Storage::disk('public')->delete(array_filter([$magazine->cover, $magazine->file_path]));

        $magazine->delete();

        return redirect()->route('magazines.index')
            ->with('success', 'Majalah berhasil dihapus.');
    }
}
