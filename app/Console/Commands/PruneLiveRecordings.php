<?php

namespace App\Console\Commands;

use App\Enums\LiveRecordingStatus;
use App\Models\LiveRecording;
use App\Support\LiveRecording\RecordingPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneLiveRecordings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-live-recordings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete live recordings older than the configured retention period';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) config('live-recording.retention_days', 14));
        $cutoff = now()->subDays($days);
        $disk = Storage::disk(RecordingPaths::diskName());

        $deleted = 0;

        // A crash during remux can leave rows stuck in "finalizing". Turn them
        // into failures so they are eligible for the normal retention cleanup.
        LiveRecording::query()
            ->where('status', LiveRecordingStatus::Finalizing->value)
            ->where('updated_at', '<=', now()->subHours(2))
            ->update([
                'status' => LiveRecordingStatus::Failed->value,
                'error_message' => 'Finalisasi tidak selesai (proses berhenti).',
            ]);

        LiveRecording::query()
            ->whereIn('status', [
                LiveRecordingStatus::Completed->value,
                LiveRecordingStatus::Failed->value,
            ])
            ->where('created_at', '<=', $cutoff)
            ->orderBy('id')
            ->chunkById(100, function ($recordings) use ($disk, &$deleted): void {
                foreach ($recordings as $recording) {
                    $paths = [
                        $recording->file_path,
                        $recording->segment_path,
                        RecordingPaths::file($recording->live_slug, 'log'),
                    ];

                    foreach ($paths as $path) {
                        if (is_string($path) && $path !== '' && $disk->exists($path)) {
                            $disk->delete($path);
                        }
                    }

                    $recording->delete();
                    $deleted++;
                }
            });

        $this->info("{$deleted} rekaman lebih lama dari {$days} hari dihapus.");
        $this->info('Prune completed.');

        return self::SUCCESS;
    }
}
