<?php

namespace App\Console\Commands;

use App\Models\SheetIntegration;
use App\Services\SheetIntegration\SheetSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncGoogleSheets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-google-sheets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-sync Master Data with their linked Google Sheets';

    /**
     * Execute the console command.
     */
    public function handle(SheetSyncService $service): int
    {
        $integrations = SheetIntegration::query()
            ->get()
            ->filter(fn (SheetIntegration $integration): bool => $integration->mode->isAuto())
            ->filter(fn (SheetIntegration $integration): bool => $integration->isConfigured());

        if ($integrations->isEmpty()) {
            $this->info('Tidak ada integrasi sheet dengan mode Auto-Sync yang siap dijalankan.');

            return self::SUCCESS;
        }

        foreach ($integrations as $integration) {
            try {
                $result = $service->apply($integration, $integration->auto_direction);
            } catch (Throwable $exception) {
                $this->error($integration->master_data->label().': '.$exception->getMessage());

                continue;
            }

            $this->info(sprintf(
                '%s: %d data diterapkan, %d dilewati (%s).',
                $integration->master_data->label(),
                $result['applied'],
                $result['skipped'],
                $integration->auto_direction,
            ));
        }

        return self::SUCCESS;
    }
}
