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
            ->filter(fn (SheetIntegration $integration): bool => $integration->auto_sync || $integration->mode->isAuto())
            ->filter(fn (SheetIntegration $integration): bool => $integration->isConfigured());

        if ($integrations->isEmpty()) {
            $this->info('Tidak ada integrasi sheet dengan Auto-Sync yang siap dijalankan.');

            return self::SUCCESS;
        }

        foreach ($integrations as $integration) {
            $label = $integration->master_data->label();

            try {
                if ($integration->auto_sync) {
                    $result = $service->fillMissing($integration);
                } else {
                    $result = $service->apply($integration, $integration->auto_direction);
                }
            } catch (Throwable $exception) {
                $this->error($label.': '.$exception->getMessage());

                continue;
            }

            if ($integration->auto_sync) {
                $this->info(sprintf(
                    '%s: %d data diisi ke Database, %d data diisi ke Sheet (auto-sync).',
                    $label,
                    $result['to_database'],
                    $result['to_sheet'],
                ));

                continue;
            }

            $this->info(sprintf(
                '%s: %d data diterapkan, %d dilewati (%s).',
                $label,
                $result['applied'],
                $result['skipped'],
                $integration->auto_direction,
            ));
        }

        return self::SUCCESS;
    }
}
