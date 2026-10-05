<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Spreadsheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterDataExportImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_teater_can_be_exported_to_excel(): void
    {
        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => '2026/01/01',
            'setlist' => 'Pajama Drive',
            'unit_song' => 'Tenshi no Shippo',
            'is_global_center' => 1,
            'is_us_center' => 0,
            'is_the_show_has_event' => 'STS Oniel',
            'additional_information' => 'Blocking A',
        ]);

        $this->actingAs(User::factory()->create());

        $response = $this->get(route('show-teater.export'));
        $response->assertOk();

        $rows = $this->parseSpreadsheet($response->streamedContent());

        $this->assertSame(
            ['Show ID', 'Tanggal', 'Setlist', 'Unit Song', 'Global Center', 'US Center', 'Event', 'Info Tambahan'],
            $rows[0],
        );

        $this->assertCount(8, $rows[1]);
        $this->assertSame('1', (string) $rows[1][0]);
        $this->assertStringContainsString('2026', $rows[1][1]);
        $this->assertSame('Pajama Drive', $rows[1][2]);
        $this->assertSame('Tenshi no Shippo', $rows[1][3]);
        $this->assertSame('Yes', $rows[1][4]);
        $this->assertSame('-', $rows[1][5]);
        $this->assertSame('STS Oniel', $rows[1][6]);
        $this->assertSame('Blocking A', $rows[1][7]);
    }

    public function test_live_streaming_can_be_exported_to_excel(): void
    {
        DB::table('live_streaming')->insert([
            'live_id' => 'live-export-1',
            'platform' => 'Showroom',
            'live_date' => '2026-02-02',
            'duration' => 95,
            'additional_info' => 'Radio',
        ]);

        $this->actingAs(User::factory()->create());

        $response = $this->get(route('live-streaming.export'));
        $response->assertOk();

        $rows = $this->parseSpreadsheet($response->streamedContent());

        $this->assertSame(['Platform', 'Live Date', 'Duration (HH:MM)', 'Additional Info'], $rows[0]);
        $this->assertSame('Showroom', $rows[1][0]);
        $this->assertStringContainsString('2026', $rows[1][1]);
        $this->assertSame('01:35', $rows[1][2]);
        $this->assertSame('Radio', $rows[1][3]);
    }

    public function test_meet_greet_events_can_be_exported_to_excel(): void
    {
        DB::table('meet_greet_events')->insert([
            'event_name' => 'MG Export',
            'event_type' => 'meet-greet',
            'event_date' => '2026-03-03',
            'location' => 'Jakarta',
            'purchase_link' => 'https://example.com/buy',
        ]);

        $this->actingAs(User::factory()->create());

        $response = $this->get(route('meet-greet-events.export'));
        $response->assertOk();

        $rows = $this->parseSpreadsheet($response->streamedContent());

        $this->assertSame(['Event Name', 'Location', 'Type', 'Event Date(s)', 'Ticket Sale', 'Purchase Link'], $rows[0]);
        $this->assertSame('MG Export', $rows[1][0]);
        $this->assertSame('Jakarta', $rows[1][1]);
        $this->assertSame('Meet & Greet Festival', $rows[1][2]);
        $this->assertStringContainsString('2026', $rows[1][3]);
        $this->assertSame('–', $rows[1][4]);
        $this->assertSame('https://example.com/buy', $rows[1][5]);
    }

    public function test_concert_events_can_be_exported_to_excel(): void
    {
        DB::table('concert_events')->insert([
            'event_name' => 'Concert Export',
            'event_date' => '2026-04-04',
            'location' => 'Jakarta',
            'status' => 'on-air',
            'purchase_link' => 'https://example.com/tix',
        ]);

        $this->actingAs(User::factory()->create());

        $response = $this->get(route('concert-events.export'));
        $response->assertOk();

        $rows = $this->parseSpreadsheet($response->streamedContent());

        $this->assertSame(['Event Name', 'Date', 'Location', 'Status', 'Purchase Link'], $rows[0]);
        $this->assertSame('Concert Export', $rows[1][0]);
        $this->assertStringContainsString('2026', $rows[1][1]);
        $this->assertSame('Jakarta', $rows[1][2]);
        $this->assertSame('On-Air', $rows[1][3]);
        $this->assertSame('https://example.com/tix', $rows[1][4]);
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function parseSpreadsheet(string $content, string $extension = 'xlsx'): array
    {
        $path = tempnam(sys_get_temp_dir(), 'read').'.'.$extension;
        file_put_contents($path, $content);

        $rows = Spreadsheet::rows(new UploadedFile($path, 'export.'.$extension, null, null, true));

        @unlink($path);

        return $rows;
    }
}
