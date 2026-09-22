<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\TriviaRequest;
use App\Models\Trivia;
use App\Support\ImageOptimizer;
use App\Support\ListingQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TriviaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListingQuery::from($request, ['title', 'created_at'], 'created_at');

        $trivias = Trivia::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('description', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('content.trivia.index', [
            'trivias' => $trivias,
            'filters' => $filters,
        ]);
    }

    public function store(TriviaRequest $request): RedirectResponse
    {
        Gate::authorize('create', Trivia::class);

        Trivia::query()->create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'image' => $request->hasFile('image') ? ImageOptimizer::store($request->file('image'), 'trivia') : null,
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
        ]);

        return redirect()->route('content.trivia.index')
            ->with('success', 'Trivia berhasil ditambahkan.');
    }

    public function update(TriviaRequest $request, Trivia $trivia): RedirectResponse
    {
        Gate::authorize('update', $trivia);

        $data = [
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
        ];

        if ($request->hasFile('image')) {
            if ($trivia->image) {
                Storage::disk('public')->delete($trivia->image);
            }

            $data['image'] = ImageOptimizer::store($request->file('image'), 'trivia');
        }

        $trivia->update($data);

        return redirect()->route('content.trivia.index')
            ->with('success', 'Trivia berhasil diupdate.');
    }

    public function destroy(Trivia $trivia): RedirectResponse
    {
        Gate::authorize('delete', $trivia);

        if ($trivia->image) {
            Storage::disk('public')->delete($trivia->image);
        }

        $trivia->delete();

        return redirect()->route('content.trivia.index')
            ->with('success', 'Trivia berhasil dihapus.');
    }
}
