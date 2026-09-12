<?php

namespace App\Http\Controllers;

use App\Http\Requests\LiveStreamingRequest;
use App\Models\LiveStreaming;
use App\Support\ListingQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LiveStreamingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListingQuery::from($request, ['platform', 'live_date', 'duration'], 'live_date', [
            'platform' => '',
            'date_from' => '',
            'date_to' => '',
        ]);

        $liveStreams = LiveStreaming::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nestedQuery) use ($filters): void {
                    $nestedQuery->where('platform', 'like', "%{$filters['search']}%")
                        ->orWhere('additional_info', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['platform'] !== '', function ($query) use ($filters): void {
                $query->where('platform', $filters['platform']);
            })
            ->when($filters['date_from'] !== '', function ($query) use ($filters): void {
                $query->whereDate('live_date', '>=', $filters['date_from']);
            })
            ->when($filters['date_to'] !== '', function ($query) use ($filters): void {
                $query->whereDate('live_date', '<=', $filters['date_to']);
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->orderBy('id', 'desc')
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('live-streaming.index', [
            'liveStreams' => $liveStreams,
            'filters' => $filters,
        ]);
    }

    public function store(LiveStreamingRequest $request): RedirectResponse
    {
        LiveStreaming::create($request->validated());

        return redirect()->route('live-streaming.index')
            ->with('success', 'Live streaming berhasil ditambahkan.');
    }

    public function update(LiveStreamingRequest $request, LiveStreaming $liveStreaming): RedirectResponse
    {
        $liveStreaming->update($request->validated());

        return redirect()->route('live-streaming.index')
            ->with('success', 'Live streaming berhasil diupdate.');
    }

    public function fetchManually(Request $request): JsonResponse
    {
        try {
            $exitCode = Artisan::call('app:fetch-streaming-info');
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => $output !== '' ? $output : 'Gagal mengambil data live streaming.',
                ], 500);
            }

            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => $output !== '' ? $output : 'Data berhasil di-fetch!',
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: '.$e->getMessage(),
            ], 500);
        }
    }
}
