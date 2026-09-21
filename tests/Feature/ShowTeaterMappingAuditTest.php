<?php

namespace Tests\Feature;

use App\Models\ShowTeaterCategories;
use App\Support\ShowTeaterMappingAudit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShowTeaterMappingAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reports_unmatched_setlists_and_unit_songs(): void
    {
        $pajamaDrive = ShowTeaterCategories::factory()->create(['name' => 'Pajama Drive']);
        $renaiKinshi = ShowTeaterCategories::factory()->create(['name' => 'Renai Kinshi Jourei']);

        ShowTeaterCategories::factory()->unitSong($pajamaDrive)->create(['name' => 'Tenshi no Shippo']);
        ShowTeaterCategories::factory()->unitSong($pajamaDrive)->create(['name' => 'Relax']);
        ShowTeaterCategories::factory()->unitSong($renaiKinshi)->create(['name' => 'Relax']);

        $this->insertShow(1, 'Pajama Drive', 'Tenshi no Shippo');
        $this->insertShow(2, 'Unknown Set', 'Relax');
        $this->insertShow(3, 'Unknown Set', 'Mystery Song');
        $this->insertShow(4, 'Pajama Drive', 'Tenshi no Shippo; Mystery Song');
        $this->insertShow(5, '  pajama   drive ', 'tenshi no shippo');

        DB::table('show_teater')->insert([
            'show_id' => 6,
            'show_date' => '2026/01/01',
            'setlist' => 'Pajama Drive',
            'unit_song' => 'Tenshi no Shippo',
            'deleted_at' => now(),
        ]);

        $report = (new ShowTeaterMappingAudit)->build();

        $this->assertSame(5, $report['scanned_shows']);
        $this->assertSame(1, $report['double_unit_song_rows']);

        $this->assertSame(3, $report['setlist']['matched']);
        $this->assertSame(2, $report['setlist']['unmatched']);
        $this->assertSame(1, $report['setlist']['unmatched_distinct']);
        $this->assertSame([['name' => 'Unknown Set', 'count' => 2]], $report['unmatched_setlists']);

        $this->assertSame(6, $report['unit_song']['total_tokens']);
        $this->assertSame(3, $report['unit_song']['matched_scoped']);
        $this->assertSame(1, $report['unit_song']['matched_global_only']);
        $this->assertSame(2, $report['unit_song']['unmatched']);
        $this->assertSame(1, $report['unit_song']['unmatched_distinct']);
        $this->assertSame([['name' => 'Mystery Song', 'count' => 2]], $report['unmatched_unit_songs']);

        $this->assertContains(['name' => 'relax', 'setlist_count' => 2], $report['ambiguous_unit_song_names']);
    }

    public function test_the_command_outputs_json(): void
    {
        $setlist = ShowTeaterCategories::factory()->create(['name' => 'Pajama Drive']);
        ShowTeaterCategories::factory()->unitSong($setlist)->create(['name' => 'Tenshi no Shippo']);
        $this->insertShow(1, 'Pajama Drive', 'Tenshi no Shippo');

        $this->artisan('app:audit-show-teater-mapping', ['--json' => true])
            ->assertExitCode(0)
            ->expectsOutputToContain('"scanned_shows": 1');
    }

    private function insertShow(int $showId, string $setlist, ?string $unitSong): void
    {
        DB::table('show_teater')->insert([
            'show_id' => $showId,
            'show_date' => '2026/01/01',
            'setlist' => $setlist,
            'unit_song' => $unitSong,
        ]);
    }
}
