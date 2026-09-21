<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Prediksi unit song & center berdasarkan show terakhir dengan setlist yang sama.
 */
final class ShowTeaterUnitSongPredictor
{
    /**
     * Data show terakhir per setlist (beserta unit song & center).
     *
     * @return array<string, array{unit_song: ?string, is_global_center: bool, is_us_center: bool}>
     */
    public function mapBySetlist(): array
    {
        $rows = DB::table('show_teater')
            ->whereNull('deleted_at')
            ->orderByRaw(ShowDate::sqlExpression().' asc')
            ->orderBy('show_id', 'asc')
            ->get(['setlist', 'unit_song', 'is_global_center', 'is_us_center']);

        $map = [];

        foreach ($rows as $row) {
            $setlist = trim((string) $row->setlist);

            if ($setlist === '') {
                continue;
            }

            // Iterasi ascending: nilai terakhir menang.
            $entry = $map[$setlist] ?? [
                'unit_song' => null,
                'is_global_center' => false,
                'is_us_center' => false,
            ];

            // Unit song: pakai yang terakhir tidak kosong (show hasil scrape bisa kosong).
            $unitSong = trim((string) ($row->unit_song ?? ''));

            if ($unitSong !== '') {
                $entry['unit_song'] = $unitSong;
            }

            // Center: bawa nilai terakhir yang terisi (NULL pada data scrape tidak menimpa).
            if ($row->is_global_center !== null) {
                $entry['is_global_center'] = (int) $row->is_global_center === 1;
            }

            if ($row->is_us_center !== null) {
                $entry['is_us_center'] = (int) $row->is_us_center === 1;
            }

            $map[$setlist] = $entry;
        }

        return $map;
    }

    public function predictFor(string $setlist): ?string
    {
        return $this->mapBySetlist()[trim($setlist)]['unit_song'] ?? null;
    }
}
