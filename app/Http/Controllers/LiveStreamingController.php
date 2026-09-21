<?php

namespace App\Http\Controllers;

use App\Http\Requests\LiveStreamingRequest;
use App\Models\LiveStreaming;
use App\Support\ListingQuery;
use App\Support\Spreadsheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function export(): StreamedResponse
    {
        $streams = LiveStreaming::query()->orderByDesc('live_date')->get();

        return Spreadsheet::download('live-streaming-'.now()->format('Ymd-His').'.xlsx', [
            'Platform',
            'Live Date',
            'Duration (HH:MM)',
            'Additional Info',
        ], $streams->map(static fn (LiveStreaming $stream): array => [
            $stream->platform instanceof \BackedEnum ? $stream->platform->value : (string) $stream->platform,
            $stream->live_date?->translatedFormat('d F Y'),
            $stream->duration !== null
                ? sprintf('%02d:%02d', intdiv($stream->duration, 60), $stream->duration % 60)
                : '–',
            $stream->additional_info ?: '–',
        ]));
    }

    public function store(LiveStreamingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['live_id'] = filled($data['live_id'] ?? null) ? $data['live_id'] : $this->generateLiveId();

        LiveStreaming::create($data);

        return redirect()->route('live-streaming.index')
            ->with('success', 'Live streaming berhasil ditambahkan.');
    }

    public function update(LiveStreamingRequest $request, LiveStreaming $liveStreaming): RedirectResponse
    {
        $data = $request->validated();
        $data['live_id'] = filled($data['live_id'] ?? null)
            ? $data['live_id']
            : ($liveStreaming->live_id ?: $this->generateLiveId());

        $liveStreaming->update($data);

        return redirect()->route('live-streaming.index')
            ->with('success', 'Live streaming berhasil diupdate.');
    }

    /**
     * Manual entries have no external identifier, so give them a unique one to keep
     * the live_id column (and its unique index) meaningful.
     */
    private function generateLiveId(): string
    {
        do {
            $candidate = 'manual-'.now()->format('ymdHis').'-'.Str::lower(Str::random(4));
        } while (LiveStreaming::query()->where('live_id', $candidate)->exists());

        return $candidate;
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
