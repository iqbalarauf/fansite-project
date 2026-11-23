<?php

namespace App\Console\Commands;

use App\Models\AboutSettings;
use App\Support\CheckMemberLive;
use Illuminate\Console\Command;

class CheckMemberLiveStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-member-live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the idol live status from the JKT48Connect API';

    /**
     * Execute the console command.
     */
    public function handle(CheckMemberLive $memberLive): int
    {
        $settings = AboutSettings::query()
            ->whereIn('key', ['idol_name', 'idol_shortname'])
            ->pluck('value', 'key');

        $memberNames = array_values(array_filter(
            [
                trim((string) $settings->get('idol_name')),
                trim((string) $settings->get('idol_shortname')),
            ],
            fn (string $name): bool => $name !== '',
        ));

        if ($memberNames === []) {
            $this->error('Idol name/shortname not found in about_settings.');

            return self::FAILURE;
        }

        $idolLabel = implode(' / ', $memberNames);

        // Refresh the /api/now_live cache used by the Status Live card.
        // Live sessions are intentionally not persisted here: live_streaming
        // is filled from /api/recent after a session has ended.
        $items = $memberLive->matchingItems($memberNames, refresh: true);

        if ($items === []) {
            $this->info("Tidak ada live streaming untuk {$idolLabel}.");
        } else {
            $this->info(count($items)." live streaming aktif untuk {$idolLabel}.");
        }

        $this->info('Check completed.');

        return self::SUCCESS;
    }
}
