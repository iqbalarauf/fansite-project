<?php

namespace Tests\Feature;

use App\Contracts\GoogleSheetsClient;
use App\Enums\MasterData;
use App\Enums\SyncMode;
use App\Models\ConcertEvents;
use App\Models\LiveStreaming;
use App\Models\MeetGreetEvents;
use App\Models\SheetIntegration;
use App\Models\ShowTeater;
use App\Models\User;
use App\Services\SheetIntegration\SheetSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\FakeGoogleSheetsClient;
use Tests\TestCase;

class SheetIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private FakeGoogleSheetsClient $sheets;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sheets = new FakeGoogleSheetsClient;
        $this->app->instance(GoogleSheetsClient::class, $this->sheets);
    }

    public function test_page_is_restricted_to_super_admin(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('sheet-integration.comparison'))->assertOk();

        $this->actingAs(User::factory()->contentCreator()->create());
        $this->get(route('sheet-integration.comparison'))->assertForbidden();
    }

    public function test_super_admin_can_save_spreadsheet_configuration(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::sheet-integration.comparison')
            ->set('integrations.show_teater.spreadsheet_id', 'spreadsheet-abc')
            ->set('integrations.show_teater.sheet_name', 'Show Teater')
            ->set('integrations.show_teater.mode', SyncMode::Auto->value)
            ->set('integrations.show_teater.auto_direction', SheetSyncService::DIRECTION_DATABASE_TO_SHEET)
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sheet_integrations', [
            'master_data' => MasterData::ShowTeater->value,
            'spreadsheet_id' => 'spreadsheet-abc',
            'sheet_name' => 'Show Teater',
            'mode' => SyncMode::Auto->value,
        ]);
    }

    public function test_comparison_detects_row_level_differences(): void
    {
        ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => '2026-01-01',
            'setlist' => 'Set A',
        ]);

        $this->seedShowTeaterSheet([
            ['show_id' => '1', 'show_date' => '2026-01-01', 'setlist' => 'Set B'],
            ['show_id' => '2', 'show_date' => '2026-02-02', 'setlist' => 'Set C'],
        ]);

        $this->makeIntegration(MasterData::ShowTeater);

        $this->actingAs(User::factory()->create());

        $comparisons = Livewire::test('pages::sheet-integration.comparison')
            ->call('startSync', SheetSyncService::DIRECTION_DATABASE_TO_SHEET)
            ->assertSet('direction', SheetSyncService::DIRECTION_DATABASE_TO_SHEET)
            ->get('comparisons');

        $this->assertSame('different', $comparisons['show_teater']['rows'][0]['status']);
        $this->assertSame('only_sheet', $comparisons['show_teater']['rows'][1]['status']);
        $this->assertSame(
            ['column' => 'setlist', 'database' => 'Set A', 'sheet' => 'Set B'],
            $comparisons['show_teater']['rows'][0]['differences'][0],
        );
    }

    public function test_sync_database_to_sheet_writes_database_rows(): void
    {
        ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => '2026-01-01',
            'setlist' => 'Set A',
        ]);

        $this->seedShowTeaterSheet([
            ['show_id' => '1', 'show_date' => '2026-01-01', 'setlist' => 'Set B'],
        ]);

        $this->makeIntegration(MasterData::ShowTeater);
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::sheet-integration.comparison')
            ->call('startSync', SheetSyncService::DIRECTION_DATABASE_TO_SHEET)
            ->call('applySync')
            ->assertHasNoErrors();

        $this->assertCount(1, $this->sheets->writes);
        $this->assertCount(1, $this->sheets->writes[0]['rows']);
        $this->assertSame('Set A', $this->sheets->writes[0]['rows'][0]['setlist']);
    }

    public function test_sync_sheet_to_database_updates_and_creates_records(): void
    {
        ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => '2026-01-01',
            'setlist' => 'Set A',
        ]);

        $this->seedShowTeaterSheet([
            ['show_id' => '1', 'show_date' => '2026-01-01', 'setlist' => 'Set B'],
            ['show_id' => '2', 'show_date' => '2026-02-02', 'setlist' => 'Set C'],
        ]);

        $this->makeIntegration(MasterData::ShowTeater);
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::sheet-integration.comparison')
            ->call('startSync', SheetSyncService::DIRECTION_SHEET_TO_DATABASE)
            ->call('applySync')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('show_teater', ['show_id' => 1, 'setlist' => 'Set B']);
        $this->assertDatabaseHas('show_teater', ['show_id' => 2, 'setlist' => 'Set C']);
    }

    public function test_per_row_resolution_can_override_the_default_winner(): void
    {
        ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => '2026-01-01',
            'setlist' => 'Set A',
        ]);

        $this->seedShowTeaterSheet([
            ['show_id' => '1', 'show_date' => '2026-01-01', 'setlist' => 'Set B'],
        ]);

        $this->makeIntegration(MasterData::ShowTeater);
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::sheet-integration.comparison')
            ->call('startSync', SheetSyncService::DIRECTION_DATABASE_TO_SHEET)
            ->set('resolutions.show_teater.1', 'sheet')
            ->call('applySync')
            ->assertHasNoErrors();

        $this->assertSame('Set B', $this->sheets->writes[0]['rows'][0]['setlist']);
    }

    public function test_rows_can_be_skipped_during_sync(): void
    {
        ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => '2026-01-01',
            'setlist' => 'Set A',
        ]);

        $this->seedShowTeaterSheet([
            ['show_id' => '1', 'show_date' => '2026-01-01', 'setlist' => 'Set B'],
        ]);

        $this->makeIntegration(MasterData::ShowTeater);
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::sheet-integration.comparison')
            ->call('startSync', SheetSyncService::DIRECTION_DATABASE_TO_SHEET)
            ->set('resolutions.show_teater.1', 'skip')
            ->call('applySync')
            ->assertHasNoErrors();

        $this->assertSame([], $this->sheets->writes[0]['rows']);
    }

    public function test_auto_sync_command_processes_auto_integrations(): void
    {
        LiveStreaming::query()->create([
            'live_id' => 'live-1',
            'platform' => 'Showroom',
            'live_date' => '2026-03-03',
        ]);

        $headers = MasterData::LiveStreaming->columns();
        $this->sheets->seed('spreadsheet-live', 'Live', $headers, [
            ['id' => '1', 'live_id' => 'live-1', 'platform' => 'Showroom', 'live_date' => '2026-03-03', 'duration' => '90', 'additional_info' => null],
        ]);

        SheetIntegration::factory()
            ->forMasterData(MasterData::LiveStreaming)
            ->auto()
            ->create(['spreadsheet_id' => 'spreadsheet-live', 'sheet_name' => 'Live']);

        $this->artisan('app:sync-google-sheets')->assertExitCode(0);

        $this->assertCount(1, $this->sheets->writes);
    }

    public function test_auto_sync_command_skips_disabled_and_manual_integrations(): void
    {
        $this->makeIntegration(MasterData::ShowTeater, SyncMode::Disabled);

        ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => '2026-01-01',
            'setlist' => 'Set A',
        ]);

        $this->artisan('app:sync-google-sheets')->assertExitCode(0);

        $this->assertSame([], $this->sheets->writes);
    }

    public function test_other_master_data_models_are_supported(): void
    {
        ConcertEvents::query()->create([
            'event_name' => 'Concert A',
            'event_date' => '2026-04-04',
            'location' => 'Jakarta',
            'status' => 'on-air',
        ]);

        MeetGreetEvents::query()->create([
            'event_name' => 'MG A',
            'event_date' => '2026-05-05',
        ]);

        $this->assertSame('event_name', MasterData::ConcertEvents->columns()[1]);
        $this->assertSame('id', MasterData::ConcertEvents->keyColumn());
        $this->assertSame('id', MasterData::MeetGreetEvents->keyColumn());
        $this->assertSame('event_type', MasterData::MeetGreetEvents->columns()[2]);
    }

    public function test_sync_uses_configured_header_row_and_column(): void
    {
        ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => '2026-01-01',
            'setlist' => 'Set A',
        ]);

        $this->seedShowTeaterSheet([
            ['show_id' => '1', 'show_date' => '2026-01-01', 'setlist' => 'Set B'],
        ]);

        $this->makeIntegration(MasterData::ShowTeater, headerRow: 3, headerColumn: 'B');
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::sheet-integration.comparison')
            ->set('integrations.show_teater.header_row', 3)
            ->set('integrations.show_teater.header_column', 'b')
            ->call('startSync', SheetSyncService::DIRECTION_DATABASE_TO_SHEET)
            ->assertHasNoErrors()
            ->call('applySync')
            ->assertHasNoErrors();

        $this->assertSame(3, $this->sheets->reads[0]['startRow']);
        $this->assertSame('B', $this->sheets->reads[0]['startColumn']);
        $this->assertSame(3, $this->sheets->writes[0]['startRow']);
        $this->assertSame('B', $this->sheets->writes[0]['startColumn']);
    }

    public function test_header_column_is_normalized_to_uppercase_when_saved(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::sheet-integration.comparison')
            ->set('integrations.show_teater.header_row', 2)
            ->set('integrations.show_teater.header_column', 'c')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sheet_integrations', [
            'master_data' => MasterData::ShowTeater->value,
            'header_row' => 2,
            'header_column' => 'C',
        ]);
    }

    public function test_invalid_header_column_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::sheet-integration.comparison')
            ->set('integrations.show_teater.header_column', '1')
            ->call('saveSettings')
            ->assertHasErrors('integrations.show_teater.header_column');
    }

    /**
     * @param  array<int, array<string, string|null>>  $rows
     */
    private function seedShowTeaterSheet(array $rows): void
    {
        $this->sheets->seed(
            'spreadsheet-'.MasterData::ShowTeater->value,
            'Show Teater',
            MasterData::ShowTeater->columns(),
            $rows,
        );
    }

    private function makeIntegration(
        MasterData $masterData,
        SyncMode $mode = SyncMode::Manual,
        int $headerRow = 1,
        string $headerColumn = 'A',
    ): SheetIntegration {
        $sheetName = match ($masterData) {
            MasterData::ShowTeater => 'Show Teater',
            MasterData::LiveStreaming => 'Live',
            MasterData::ConcertEvents => 'Concert',
            MasterData::MeetGreetEvents => 'Meet Greet',
        };

        return SheetIntegration::factory()
            ->forMasterData($masterData)
            ->create([
                'spreadsheet_id' => 'spreadsheet-'.$masterData->value,
                'sheet_name' => $sheetName,
                'header_row' => $headerRow,
                'header_column' => $headerColumn,
                'mode' => $mode,
            ]);
    }
}
