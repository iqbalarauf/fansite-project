<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimelineRequest;
use App\Models\Timeline;
use App\Support\ImageOptimizer;
use App\Support\ListingQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListingQuery::from($request, ['date', 'created_at'], 'date');

        $timelines = Timeline::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where('description', 'like', "%{$filters['search']}%");
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->orderByDesc('id')
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('content.timeline.index', [
            'timelines' => $timelines,
            'filters' => $filters,
        ]);
    }

    public function store(TimelineRequest $request): RedirectResponse
    {
        Gate::authorize('create', Timeline::class);

        Timeline::query()->create([
            'date' => $request->validated('date'),
            'description' => $request->validated('description'),
            'image' => $request->hasFile('image') ? ImageOptimizer::store($request->file('image'), 'timeline') : null,
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
        ]);

        return redirect()->route('content.timeline.index')
            ->with('success', 'Timeline berhasil ditambahkan.');
    }

    public function update(TimelineRequest $request, Timeline $timeline): RedirectResponse
    {
        Gate::authorize('update', $timeline);

        $data = [
            'date' => $request->validated('date'),
            'description' => $request->validated('description'),
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
        ];

        if ($request->hasFile('image')) {
            if ($timeline->image) {
                Storage::disk('public')->delete($timeline->image);
            }

            $data['image'] = ImageOptimizer::store($request->file('image'), 'timeline');
        }

        $timeline->update($data);

        return redirect()->route('content.timeline.index')
            ->with('success', 'Timeline berhasil diupdate.');
    }

    public function destroy(Timeline $timeline): RedirectResponse
    {
        Gate::authorize('delete', $timeline);

        if ($timeline->image) {
            Storage::disk('public')->delete($timeline->image);
        }

        $timeline->delete();

        return redirect()->route('content.timeline.index')
            ->with('success', 'Timeline berhasil dihapus.');
    }
}
