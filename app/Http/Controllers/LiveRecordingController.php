<?php

namespace App\Http\Controllers;

use App\Enums\LiveRecordingStatus;
use App\Models\LiveRecording;
use App\Support\ListingQuery;
use App\Support\LiveRecording\Ffmpeg;
use App\Support\LiveRecording\RecordingPaths;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LiveRecordingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListingQuery::from(
            $request,
            ['started_at', 'member_name', 'platform', 'duration_seconds', 'file_size'],
            'started_at',
            [
                'platform' => '',
                'status' => '',
                'date_from' => '',
                'date_to' => '',
            ],
        );

        $recordings = LiveRecording::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nestedQuery) use ($filters): void {
                    $nestedQuery->where('member_name', 'like', "%{$filters['search']}%")
                        ->orWhere('live_slug', 'like', "%{$filters['search']}%")
                        ->orWhere('url_key', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['platform'] !== '', function ($query) use ($filters): void {
                $query->where('platform', $filters['platform']);
            })
            ->when($filters['status'] !== '', function ($query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when($filters['date_from'] !== '', function ($query) use ($filters): void {
                $query->whereDate('started_at', '>=', $filters['date_from']);
            })
            ->when($filters['date_to'] !== '', function ($query) use ($filters): void {
                $query->whereDate('started_at', '<=', $filters['date_to']);
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->orderByDesc('id')
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('live-recordings.index', [
            'recordings' => $recordings,
            'filters' => $filters,
            'statuses' => LiveRecordingStatus::cases(),
            'retentionDays' => max(1, (int) config('live-recording.retention_days', 14)),
            'recordingEnabled' => (bool) config('live-recording.enabled'),
        ]);
    }

    public function preview(LiveRecording $recording): BinaryFileResponse
    {
        $path = $recording->absolutePath();

        abort_if($path === null, 404, 'File rekaman tidak ditemukan.');

        return response()->file($path, ['Content-Type' => 'video/mp4']);
    }

    public function download(LiveRecording $recording): BinaryFileResponse
    {
        $path = $recording->absolutePath();

        abort_if($path === null, 404, 'File rekaman tidak ditemukan.');

        return response()->download($path, $recording->downloadName(), ['Content-Type' => 'video/mp4']);
    }

    public function destroy(LiveRecording $recording, Ffmpeg $ffmpeg): RedirectResponse
    {
        if ($recording->status->isActive()) {
            $ffmpeg->stop($recording->pid);
        }

        $disk = Storage::disk(RecordingPaths::diskName());

        foreach ([$recording->file_path, $recording->segment_path, RecordingPaths::file($recording->live_slug, 'log')] as $path) {
            if (is_string($path) && $path !== '' && $disk->exists($path)) {
                $disk->delete($path);
            }
        }

        $recording->delete();

        return redirect()->route('live-recordings.index')
            ->with('success', 'Rekaman berhasil dihapus.');
    }
}
