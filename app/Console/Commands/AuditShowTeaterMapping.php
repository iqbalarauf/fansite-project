<?php

namespace App\Console\Commands;

use App\Support\ShowTeaterMappingAudit;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:audit-show-teater-mapping {--json : Output the raw report as JSON} {--limit=20 : Maximum rows listed per section}')]
#[Description('Audit historical show_teater rows whose setlist/unit_song do not map to show_teater_categories')]
class AuditShowTeaterMapping extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ShowTeaterMappingAudit $audit): int
    {
        $report = $audit->build();

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $limit = max(0, (int) $this->option('limit'));

        $this->info('Audit Show Teater ↔ Kategori');
        $this->line('Show dipindai: '.$report['scanned_shows']
            .' | Punya unit song: '.$report['shows_with_unit_song']
            .' | Double unit song: '.$report['double_unit_song_rows']);
        $this->newLine();

        $this->table(['Bagian', 'Cocok', 'Tak cocok', 'Tak cocok (unik)'], [
            ['Setlist', $report['setlist']['matched'], $report['setlist']['unmatched'], $report['setlist']['unmatched_distinct']],
            [
                'Unit song',
                $report['unit_song']['matched_scoped'].' (scoped) + '.$report['unit_song']['matched_global_only'].' (global saja)',
                $report['unit_song']['unmatched'],
                $report['unit_song']['unmatched_distinct'],
            ],
        ]);

        $this->line('Cocok via jp_name: setlist '.$report['setlist']['matched_by_jp_name']
            .' | unit song '.$report['unit_song']['matched_by_jp_name']);

        $this->renderList('Setlist tak terpetakan', $report['unmatched_setlists'], $limit);
        $this->renderList('Unit song tak terpetakan', $report['unmatched_unit_songs'], $limit);
        $this->renderList('Nama unit song ambigu (ada di >1 setlist)', $report['ambiguous_unit_song_names'], $limit, 'setlist_count');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function renderList(string $heading, array $rows, int $limit, string $countKey = 'count'): void
    {
        $this->newLine();
        $this->info($heading.' ('.count($rows).')');

        if ($rows === []) {
            $this->line('  —');

            return;
        }

        $table = array_map(
            static fn (array $row): array => ['name' => $row['name'], 'count' => $row[$countKey]],
            array_slice($rows, 0, $limit),
        );

        $this->table(['Nama', $countKey === 'count' ? 'Jumlah' : 'Jumlah setlist'], $table);

        if ($limit > 0 && count($rows) > $limit) {
            $this->line('  … dan '.(count($rows) - $limit).' lainnya.');
        }
    }
}
