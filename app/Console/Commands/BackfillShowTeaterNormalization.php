<?php

namespace App\Console\Commands;

use App\Support\ShowTeaterNormalizer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:backfill-show-teater-normalization {--dry-run : Report without writing changes}')]
#[Description('Backfill show_teater.setlist_id and the show_teater_unit_song pivot from the legacy text columns')]
class BackfillShowTeaterNormalization extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ShowTeaterNormalizer $normalizer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — tidak ada perubahan yang ditulis.');
        }

        $result = $normalizer->backfill($dryRun);

        $this->table(['Metrik', 'Nilai'], [
            ['setlist_id terisi', $result['setlist_id_updated']],
            ['baris pivot unit song', $result['unit_song_rows']],
            ['setlist tak terpetakan', $result['unmatched_setlists']],
            ['unit song tak terpetakan', $result['unmatched_unit_songs']],
        ]);

        if ($result['unmatched_unit_songs'] > 0 || $result['unmatched_setlists'] > 0) {
            $this->warn('Masih ada data tak terpetakan — kolom teks tetap dipakai sebagai fallback.');
        }

        return self::SUCCESS;
    }
}
