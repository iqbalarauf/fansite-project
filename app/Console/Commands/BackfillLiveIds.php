<?php

namespace App\Console\Commands;

use App\Models\LiveStreaming;
use Illuminate\Console\Command;

class BackfillLiveIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-live-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill missing live_id values on live_streaming rows with a unique manual identifier';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = 0;

        LiveStreaming::query()
            ->whereNull('live_id')
            ->orderBy('id')
            ->chunkById(200, function ($streams) use (&$updated): void {
                foreach ($streams as $stream) {
                    $liveDate = $stream->live_date?->format('ymd') ?? 'unknown';

                    $stream->forceFill([
                        'live_id' => "manual-{$liveDate}-{$stream->id}",
                    ])->save();

                    $updated++;
                }
            });

        $this->info("{$updated} live_id dibuat untuk baris tanpa live_id.");

        return self::SUCCESS;
    }
}
