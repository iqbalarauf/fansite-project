<?php

namespace App\Http\Controllers;

use App\Models\LiveStreaming;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LiveStreamingController extends Controller
{
    public function index()
    {
        $liveStreams = LiveStreaming::orderBy('live_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('LiveStreaming/Index', [
            'liveStreams' => $liveStreams,
        ]);
    }

    public function create()
    {
        return Inertia::render('LiveStreaming/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:IDN App,Showroom',
            'live_date' => 'required|date',
            'duration' => 'nullable|integer|min:0',
            'additional_info' => 'nullable|string',
        ]);

        LiveStreaming::create($validated);

        return redirect()->route('live-streaming.index')
            ->with('success', 'Live streaming data created successfully.');
    }

    public function edit(LiveStreaming $liveStreaming)
    {
        return Inertia::render('LiveStreaming/Edit', [
            'liveStream' => $liveStreaming,
        ]);
    }

    public function update(Request $request, LiveStreaming $liveStreaming)
    {
        $validated = $request->validate([
            'platform' => 'required|in:IDN App,Showroom',
            'live_date' => 'required|date',
            'duration' => 'nullable|integer|min:0',
            'additional_info' => 'nullable|string',
        ]);

        $liveStreaming->update($validated);

        return redirect()->route('live-streaming.index')
            ->with('success', 'Live streaming data updated successfully.');
    }

    public function destroy(LiveStreaming $liveStreaming)
    {
        $liveStreaming->delete();

        return redirect()->route('live-streaming.index')
            ->with('success', 'Live streaming data deleted successfully.');
    }
}
