<?php

namespace App\Console\Commands;

use App\Enums\LiveRecordingStatus;
use App\Models\AboutSettings;
use App\Models\LiveRecording;
use App\Support\CheckMemberLive;
use App\Support\LiveRecording\Ffmpeg;
use App\Support\LiveRecording\RecordingPaths;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FinalizeLiveRecordings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:finalize-live-recordings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop finished live recordings and remux them from MPEG-TS to MP4';

    /**
     * Execute the console command.
     */
    public function handle(CheckMemberLive $memberLive, Ffmpeg $ffmpeg): int
    {
        if (! config('live-recording.enabled')) {
            $this->info('Live recording is disabled (LIVE_RECORDING_ENABLED=false).');

            return self::SUCCESS;
        }

        $needsLiveCheck = LiveRecording::query()
            ->whereIn('status', [
                LiveRecordingStatus::Starting->value,
                LiveRecordingStatus::Recording->value,
            ])
            ->exists();

        // Only hit /api/now_live when there is an active recording to check.
        $liveSlugs = $needsLiveCheck ? $this->liveSlugs($memberLive) : [];
        $now = now();
        $disk = Storage::disk(RecordingPaths::diskName());

        if ($liveSlugs === null) {
            $this->warn('API /api/now_live tidak tersedia; rekaman aktif tidak dihentikan pada pengecekan ini.');
        }

        $stopped = 0;
        $finalized = 0;
        $failed = 0;

        // Phase 1: decide which active recordings must stop.
        $recordings = LiveRecording::query()
            ->whereIn('status', [
                LiveRecordingStatus::Starting->value,
                LiveRecordingStatus::Recording->value,
            ])
            ->orderBy('id')
            ->get();

        foreach ($recordings as $recording) {
            if ($recording->status === LiveRecordingStatus::Starting) {
                $this->failStaleStarting($recording, $now);
                $failed += $recording->status === LiveRecordingStatus::Failed ? 1 : 0;

                continue;
            }

            $stillLive = $liveSlugs === null || isset($liveSlugs[$recording->live_slug]);
            $processAlive = $ffmpeg->isRunning($recording->pid);
            $expired = $this->exceededMaxDuration($recording, $now);

            if ($processAlive) {
                if ($stillLive && ! $expired) {
                    continue;
                }

                // The API may briefly omit a live session; wait before stopping
                // so a single glitch does not cut a recording short.
                if (! $stillLive && ! $expired && ! $this->passedFinalizeDelay($recording, $now)) {
                    continue;
                }
            }

            $ffmpeg->stop($recording->pid);

            $recording->update([
                'status' => LiveRecordingStatus::Stopping,
                'ended_at' => $recording->ended_at ?? $now,
            ]);

            $stopped++;
            $this->info("Menghentikan rekaman {$recording->live_slug}.");
        }

        // Phase 2: remux recordings that have finished flushing their TS file.
        $stopping = LiveRecording::query()
            ->where('status', LiveRecordingStatus::Stopping->value)
            ->orderBy('id')
            ->get();

        foreach ($stopping as $recording) {
            if (! $this->isReadyToFinalize($recording, $ffmpeg, $now)) {
                continue;
            }

            $segment = (string) $recording->segment_path;

            if ($segment === '' || ! $disk->exists($segment)) {
                $recording->update([
                    'status' => LiveRecordingStatus::Failed,
                    'ended_at' => $recording->ended_at ?? $now,
                    'error_message' => 'File rekaman TS tidak ditemukan.',
                ]);
                $failed++;

                continue;
            }

            $recording->update(['status' => LiveRecordingStatus::Finalizing]);

            $outputPath = RecordingPaths::mp4For($segment);

            try {
                if (! $ffmpeg->remux($disk->path($segment), $disk->path($outputPath))) {
                    throw new \RuntimeException('Gagal mengubah rekaman TS menjadi MP4.');
                }

                $recording->update([
                    'status' => LiveRecordingStatus::Completed,
                    'file_path' => $outputPath,
                    'file_size' => $disk->size($outputPath),
                    'duration_seconds' => $this->durationSeconds($recording),
                    'ended_at' => $recording->ended_at ?? $now,
                    'error_message' => null,
                ]);

                $disk->delete($segment);
                $finalized++;
                $this->info("Rekaman {$recording->live_slug} selesai.");
            } catch (Throwable $exception) {
                $recording->update([
                    'status' => LiveRecordingStatus::Failed,
                    'error_message' => $exception->getMessage(),
                ]);
                $failed++;
                $this->error("Gagal finalisasi {$recording->live_slug}: {$exception->getMessage()}");
            }
        }

        $this->info("Dihentikan: {$stopped}, selesai: {$finalized}, gagal: {$failed}.");
        $this->info('Finalize completed.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, true>|null Null when the API is unavailable.
     */
    private function liveSlugs(CheckMemberLive $memberLive): ?array
    {
        $memberNames = $this->memberNames();

        if ($memberNames === []) {
            return [];
        }

        $items = $memberLive->freshMatchingItems($memberNames);

        if ($items === null) {
            return null;
        }

        $slugs = [];

        foreach ($items as $item) {
            $slug = trim((string) ($item['slug'] ?? ''));

            if ($slug !== '') {
                $slugs[$slug] = true;
            }
        }

        return $slugs;
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

    private function failStaleStarting(LiveRecording $recording, CarbonInterface $now): void
    {
        $createdAt = $recording->created_at;

        if ($createdAt === null || $createdAt->gt($now->copy()->subMinutes(10))) {
            return;
        }

        $recording->update([
            'status' => LiveRecordingStatus::Failed,
            'error_message' => 'Proses ffmpeg tidak pernah berhasil dimulai.',
        ]);
    }

    private function passedFinalizeDelay(LiveRecording $recording, CarbonInterface $now): bool
    {
        $reference = $recording->last_seen_live_at ?? $recording->started_at ?? $recording->created_at;

        if ($reference === null) {
            return true;
        }

        $delay = max(0, (int) config('live-recording.finalize_delay_minutes', 5));

        return $reference->lte($now->copy()->subMinutes($delay));
    }

    private function exceededMaxDuration(LiveRecording $recording, CarbonInterface $now): bool
    {
        $maxMinutes = (int) config('live-recording.max_duration_minutes', 360);

        if ($maxMinutes <= 0 || $recording->started_at === null) {
            return false;
        }

        return $recording->started_at->lte($now->copy()->subMinutes($maxMinutes));
    }

    private function isReadyToFinalize(LiveRecording $recording, Ffmpeg $ffmpeg, CarbonInterface $now): bool
    {
        if ($ffmpeg->isRunning($recording->pid)) {
            return false;
        }

        $endedAt = $recording->ended_at ?? $recording->updated_at ?? $now;
        $grace = max(0, (int) config('live-recording.stop_grace_seconds', 20));

        return $endedAt->lte($now->copy()->subSeconds($grace));
    }

    private function durationSeconds(LiveRecording $recording): ?int
    {
        $endedAt = $recording->ended_at ?? now();

        if ($recording->started_at === null) {
            return null;
        }

        return max(0, (int) $recording->started_at->diffInSeconds($endedAt));
    }
}
