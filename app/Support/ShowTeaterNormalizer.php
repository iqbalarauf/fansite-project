<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Backfill & sinkronisasi `show_teater.setlist_id` dan pivot `show_teater_unit_song`
 * dari kolom teks `setlist`/`unit_song` (pencocokan name atau jp_name).
 *
 * Idempoten: baris pivot untuk show yang disinkronkan selalu ditulis ulang.
 * Kolom teks tetap dipertahankan sebagai fallback untuk data yang tak terpetakan.
 */
final class ShowTeaterNormalizer
{
    public function __construct(private readonly ?ShowTeaterCategoryResolver $resolver = null) {}

    /**
     * @return array{setlist_id_updated: int, unit_song_rows: int, unmatched_setlists: int, unmatched_unit_songs: int, dry_run: bool}
     */
    public function backfill(bool $dryRun = false): array
    {
        $resolver = $this->resolver ?? new ShowTeaterCategoryResolver;

        $totals = ['setlist_id_updated' => 0, 'unit_song_rows' => 0, 'unmatched_setlists' => 0, 'unmatched_unit_songs' => 0];

        foreach (DB::table('show_teater')->get(['show_id', 'setlist', 'unit_song']) as $show) {
            $this->syncRow($resolver, $show, $dryRun, $totals);
        }

        return $totals + ['dry_run' => $dryRun];
    }

    public function syncShow(int $showId): void
    {
        $show = DB::table('show_teater')->where('show_id', $showId)->first(['show_id', 'setlist', 'unit_song']);

        if ($show === null) {
            return;
        }

        $resolver = $this->resolver ?? new ShowTeaterCategoryResolver;
        $totals = ['setlist_id_updated' => 0, 'unit_song_rows' => 0, 'unmatched_setlists' => 0, 'unmatched_unit_songs' => 0];

        $this->syncRow($resolver, $show, false, $totals);
    }

    /**
     * @param  array{setlist_id_updated: int, unit_song_rows: int, unmatched_setlists: int, unmatched_unit_songs: int}  $totals
     */
    private function syncRow(ShowTeaterCategoryResolver $resolver, object $show, bool $dryRun, array &$totals): void
    {
        $setlistId = $resolver->setlistId((string) $show->setlist);

        if ($setlistId !== null) {
            $totals['setlist_id_updated']++;
        } else {
            $totals['unmatched_setlists']++;
        }

        $resolved = [];
        $position = 0;

        foreach (ShowTeaterCategoryResolver::splitUnitSongs((string) $show->unit_song) as $token) {
            $categoryId = $resolver->unitSongId($setlistId, $token);

            if ($categoryId === null) {
                $totals['unmatched_unit_songs']++;

                continue;
            }

            if (! array_key_exists($categoryId, $resolved)) {
                $resolved[$categoryId] = $position++;
            }
        }

        $totals['unit_song_rows'] += count($resolved);

        if ($dryRun) {
            return;
        }

        DB::table('show_teater')
            ->where('show_id', $show->show_id)
            ->update(['setlist_id' => $setlistId]);

        DB::table('show_teater_unit_song')->where('show_id', $show->show_id)->delete();

        if ($resolved !== []) {
            $rows = [];
            $now = now();

            foreach ($resolved as $categoryId => $pos) {
                $rows[] = [
                    'show_id' => $show->show_id,
                    'show_teater_categories_id' => $categoryId,
                    'position' => $pos,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('show_teater_unit_song')->insert($rows);
        }
    }
}
