<?php

namespace App\Console\Commands;

use App\Enums\LiveRecordingStatus;
use App\Enums\LiveStreamingPlatform;
use App\Models\AboutSettings;
use App\Models\LiveRecording;
use App\Support\CheckMemberLive;
use App\Support\LiveRecording\Ffmpeg;
use App\Support\LiveRecording\RecordingPaths;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RecordMemberLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:record-member-live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect live oshimen from /api/now_live and start recording them with ffmpeg';

    /**
     * Execute the console command.
     */
    public function handle(CheckMemberLive $memberLive, Ffmpeg $ffmpeg): int
    {
        if (! config('live-recording.enabled')) {
            $this->info('Live recording is disabled (LIVE_RECORDING_ENABLED=false).');

            return self::SUCCESS;
        }

        $memberNames = $this->memberNames();

        if ($memberNames === []) {
            $this->error('Idol name/shortname not found in about_settings.');

            return self::FAILURE;
        }

        $items = $memberLive->matchingItems($memberNames, refresh: true);

        if ($items === []) {
            $this->info('Tidak ada live streaming untuk '.implode(' / ', $memberNames).'.');

            return self::SUCCESS;
        }

        $disk = Storage::disk(RecordingPaths::diskName());
        $started = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($items as $item) {
            $slug = trim((string) ($item['slug'] ?? ''));
            $sourceUrl = $memberLive->streamUrlFor($item);
            $platform = $this->platform($item);

            if ($slug === '' || $sourceUrl === null || $platform === null) {
                $skipped++;
                $this->warn('Live item dilewati karena slug/url/platform tidak lengkap.');

                continue;
            }

            $recording = LiveRecording::query()->firstOrNew(['live_slug' => $slug]);

            if ($recording->exists) {
                // A failed initial start may be transient (API hiccup, stale URL).
                // Retry it as long as no segment was ever written; a failed
                // remux is not retried because the recording already exists.
                $retryable = $recording->status === LiveRecordingStatus::Failed
                    && $recording->segment_path === null;

                if (! $retryable) {
                    $this->refreshExisting($recording, $ffmpeg);
                    $skipped++;

                    continue;
                }
            }

            $recording->fill([
                'platform' => $platform,
                'member_name' => $this->memberName($item, $memberNames),
                'url_key' => filled($item['url_key'] ?? null) ? trim((string) $item['url_key']) : null,
                'source_url' => $sourceUrl,
                'status' => LiveRecordingStatus::Starting,
                'started_at' => $this->startedAt($item),
                'last_seen_live_at' => now(),
                'error_message' => null,
            ])->save();

            $segmentPath = RecordingPaths::file($slug, 'ts');
            $logPath = RecordingPaths::file($slug, 'log');
            $directory = RecordingPaths::directory();

            if ($directory !== '') {
                $disk->makeDirectory($directory);
            }

            $pid = $ffmpeg->start($sourceUrl, $disk->path($segmentPath), $disk->path($logPath));

            if ($pid === null) {
                $recording->update([
                    'status' => LiveRecordingStatus::Failed,
                    'error_message' => 'Gagal menjalankan ffmpeg. Periksa path ffmpeg dan log rekaman.',
                ]);
                $failed++;
                $this->error("Gagal memulai rekaman untuk {$slug}.");

                continue;
            }

            $recording->update([
                'status' => LiveRecordingStatus::Recording,
                'segment_path' => $segmentPath,
                'pid' => $pid,
            ]);

            $started++;
            $this->info("Mulai merekam {$recording->member_name} ({$slug}) dengan PID {$pid}.");
        }

        $this->info("Rekaman baru: {$started}, dilewati: {$skipped}, gagal: {$failed}.");
        $this->info('Recording check completed.');

        return self::SUCCESS;
    }

    private function refreshExisting(LiveRecording $recording, Ffmpeg $ffmpeg): void
    {
        // A completed/failed recording is kept as-is: one live session must
        // never produce two files.
        if (! $recording->status->isActive()) {
            return;
        }

        $recording->update(['last_seen_live_at' => now()]);

        if ($recording->status === LiveRecordingStatus::Recording && ! $ffmpeg->isRunning($recording->pid)) {
            $this->warn("ffmpeg untuk {$recording->live_slug} sudah berhenti; menunggu finalisasi.");
        }
    }

    /**
     * @return array<int, string>
     */
    private function memberNames(): array
    {
        $settings = AboutSettings::query()
            ->whereIn('key', ['idol_name', 'idol_shortname'])
            ->pluck('value', 'key');

        return array_values(array_filter(
            [
                trim((string) $settings->get('idol_name')),
                trim((string) $settings->get('idol_shortname')),
            ],
            fn (string $name): bool => $name !== '',
        ));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function platform(array $item): ?string
    {
        return match (strtolower(trim((string) ($item['type'] ?? '')))) {
            'idn', 'idn app' => LiveStreamingPlatform::IdnApp->value,
            'showroom' => LiveStreamingPlatform::Showroom->value,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $memberNames
     */
    private function memberName(array $item, array $memberNames): string
    {
        $name = trim((string) ($item['name'] ?? ''));

        return $name !== '' ? $name : $memberNames[0];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function startedAt(array $item): CarbonInterface
    {
        $raw = $item['started_at'] ?? null;

        if (is_string($raw) && trim($raw) !== '') {
            try {
                return Carbon::parse($raw)->utc();
            } catch (Throwable) {
                // Fall through to the current time.
            }
        }

        return now();
    }
}
