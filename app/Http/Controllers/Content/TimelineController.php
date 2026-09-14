<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimelineRequest;
use App\Models\Timeline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public function index(): View
    {
        return view('content.timeline.index', [
            'timelines' => Timeline::query()->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function store(TimelineRequest $request): RedirectResponse
    {
        Timeline::query()->create([
            'date' => $request->validated('date'),
            'description' => $request->validated('description'),
            'image' => $request->hasFile('image') ? $request->file('image')->store('timeline', 'public') : null,
        ]);

        return redirect()->route('content.timeline.index')
            ->with('success', 'Timeline berhasil ditambahkan.');
    }

    public function update(TimelineRequest $request, Timeline $timeline): RedirectResponse
    {
        $data = [
            'date' => $request->validated('date'),
            'description' => $request->validated('description'),
        ];

        if ($request->hasFile('image')) {
            if ($timeline->image) {
                Storage::disk('public')->delete($timeline->image);
            }

            $data['image'] = $request->file('image')->store('timeline', 'public');
        }

        $timeline->update($data);

        return redirect()->route('content.timeline.index')
            ->with('success', 'Timeline berhasil diupdate.');
    }

    public function destroy(Timeline $timeline): RedirectResponse
    {
        if ($timeline->image) {
            Storage::disk('public')->delete($timeline->image);
        }

        $timeline->delete();

        return redirect()->route('content.timeline.index')
            ->with('success', 'Timeline berhasil dihapus.');
    }
}
