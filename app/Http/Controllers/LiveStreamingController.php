<?php

namespace App\Http\Controllers;

use App\Models\LiveStreaming;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveStreamingController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $platform = $request->string('platform')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $sortBy = $request->string('sort_by', 'live_date')->toString();
        $sortDir = $request->string('sort_dir', 'desc')->toString();
        $perPage = (int) $request->integer('per_page', 10);

        $allowedSorts = ['platform', 'live_date', 'duration'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'live_date';
        }

        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $liveStreams = LiveStreaming::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nestedQuery) use ($search): void {
                    $nestedQuery->where('platform', 'like', "%{$search}%")
                        ->orWhere('additional_info', 'like', "%{$search}%");
                });
            })
            ->when($platform !== '', function ($query) use ($platform): void {
                $query->where('platform', $platform);
            })
            ->when($dateFrom !== '', function ($query) use ($dateFrom): void {
                $query->whereDate('live_date', '>=', $dateFrom);
            })
            ->when($dateTo !== '', function ($query) use ($dateTo): void {
                $query->whereDate('live_date', '<=', $dateTo);
            })
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('live-streaming.index', [
            'liveStreams' => $liveStreams,
            'filters' => [
                'search' => $search,
                'platform' => $platform,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'in:IDN App,Showroom'],
            'live_date' => ['required', 'date'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'additional_info' => ['nullable', 'string'],
        ]);

        LiveStreaming::create($validated);

        return redirect()->route('live-streaming.index')
            ->with('success', 'Live streaming berhasil ditambahkan.');
    }

    public function update(Request $request, LiveStreaming $liveStreaming): RedirectResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'in:IDN App,Showroom'],
            'live_date' => ['required', 'date'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'additional_info' => ['nullable', 'string'],
        ]);

        $liveStreaming->update($validated);

        return redirect()->route('live-streaming.index')
            ->with('success', 'Live streaming berhasil diupdate.');
    }
}
