<?php

namespace Tests\Feature;

use App\Models\AboutSettings;
use App\Models\TheaterReference;
use App\Models\User;
use App\Support\Timezone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchTheaterShowsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.jkt48connect.url' => 'https://jkt48connect.test',
            'services.jkt48connect.key' => 'test-key',
        ]);
    }

    public function test_it_outputs_error_if_idol_shortname_is_missing(): void
    {
        DB::table('about_settings')->where('key', 'idol_shortname')->delete();

        $this->artisan('app:fetch-theater-shows')
            ->expectsOutput('Idol shortname not found in about_settings.')
            ->assertFailed();
    }

    public function test_it_fails_when_the_api_is_not_configured(): void
    {
        config(['services.jkt48connect.url' => '', 'services.jkt48connect.key' => '']);

        $this->artisan('app:fetch-theater-shows')
            ->expectsOutput('JKT48Connect API is not configured (JKT48CONNECT_LIVE_URL / JKT48CONNECT_API_KEY).')
            ->assertFailed();
    }

    public function test_it_fetches_and_saves_theater_shows_when_lineup_matches(): void
    {
        $this->setShortname('Oniel');
        $this->fakeTheater([
            $this->show(['reference_code' => 'SHD44C', 'title' => 'Cara Meminum Ramune', 'date' => '2026-09-13']),
        ]);

        $this->artisan('app:fetch-theater-shows')
            ->expectsOutputToContain('Cara Meminum Ramune')
            ->expectsOutput('Fetch completed.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('show_teater', [
            'setlist' => 'Cara Meminum Ramune',
            'show_date' => '2026-09-13',
            'is_scraped_data' => 1,
        ]);

        $this->assertDatabaseHas('theater_references', ['reference_code' => 'SHD44C']);
        $this->assertNotNull(TheaterReference::query()->where('reference_code', 'SHD44C')->value('processed_at'));
    }

    public function test_it_skips_shows_whose_reference_was_already_recorded(): void
    {
        $this->setShortname('Oniel');

        TheaterReference::query()->create([
            'reference_code' => 'SHD44C',
            'month' => now()->month,
            'year' => now()->year,
            'processed_at' => now(),
        ]);

        $this->fakeTheater([
            $this->show(['reference_code' => 'SHD44C', 'title' => 'Cara Meminum Ramune']),
        ]);

        $countBefore = DB::table('show_teater')->count();

        $this->artisan('app:fetch-theater-shows')->assertExitCode(0);

        $this->assertSame($countBefore, DB::table('show_teater')->count());
    }

    public function test_it_skips_shows_when_the_idol_is_not_in_the_lineup(): void
    {
        $this->setShortname('Oniel');

        $this->fakeTheater([
            $this->show(['reference_code' => 'REF-LINEUP', 'lineup' => [['name' => 'Aralie'], ['name' => 'Christy']]]),
        ]);

        $this->artisan('app:fetch-theater-shows')->assertExitCode(0);

        $this->assertDatabaseMissing('show_teater', ['setlist' => 'Cara Meminum Ramune']);
        $this->assertDatabaseMissing('theater_references', ['reference_code' => 'REF-LINEUP']);
    }

    public function test_it_skips_non_show_types(): void
    {
        $this->setShortname('Oniel');

        $this->fakeTheater([
            $this->show(['reference_code' => 'REF-EVENT', 'type' => 'EVENT']),
        ]);

        $this->artisan('app:fetch-theater-shows')->assertExitCode(0);

        $this->assertDatabaseMissing('show_teater', ['setlist' => 'Cara Meminum Ramune']);
        $this->assertDatabaseMissing('theater_references', ['reference_code' => 'REF-EVENT']);
    }

    public function test_it_reports_when_the_show_is_already_synced(): void
    {
        $this->setShortname('Oniel');

        $nextShowId = (int) (DB::table('show_teater')->max('show_id') ?? 0) + 1;
        DB::table('show_teater')->insert([
            'show_id' => $nextShowId,
            'show_date' => '2026/09/13',
            'setlist' => 'Cara Meminum Ramune',
            'is_member_show' => 1,
        ]);

        $this->fakeTheater([
            $this->show(['reference_code' => 'REF-SYNCED', 'title' => 'Cara Meminum Ramune', 'date' => '2026-09-13']),
        ]);

        $countBefore = DB::table('show_teater')->count();

        $this->artisan('app:fetch-theater-shows')
            ->expectsOutputToContain('Show terbaru sudah tersinkronisasi')
            ->assertExitCode(0);

        $this->assertSame($countBefore, DB::table('show_teater')->count());
        $this->assertDatabaseHas('theater_references', ['reference_code' => 'REF-SYNCED']);
    }

    public function test_it_saves_reference_code_taken_from_the_link(): void
    {
        $this->setShortname('Oniel');

        $this->fakeTheater([
            $this->show([
                'reference_code' => null,
                'link' => 'https://jkt48.com/purchase/schedule/show?code=SH79AC',
                'title' => 'Cara Meminum Ramune',
                'date' => '2026-09-13',
            ]),
        ]);

        $this->artisan('app:fetch-theater-shows')->assertExitCode(0);

        $this->assertDatabaseHas('theater_references', ['reference_code' => 'SH79AC']);
        $this->assertDatabaseHas('show_teater', [
            'setlist' => 'Cara Meminum Ramune',
            'reference_code' => 'SH79AC',
        ]);
    }

    public function test_it_removes_old_theater_references_on_fetch(): void
    {
        $this->setShortname('Oniel');

        $previous = Timezone::nowLocal()->subMonthNoOverflow();

        TheaterReference::query()->create([
            'reference_code' => 'OLD1',
            'month' => $previous->month,
            'year' => $previous->year,
            'processed_at' => now(),
        ]);

        $this->fakeTheater([
            $this->show(['reference_code' => 'NEW1', 'title' => 'Cara Meminum Ramune', 'date' => '2026-09-13']),
        ]);

        $this->artisan('app:fetch-theater-shows')->assertExitCode(0);

        $this->assertDatabaseMissing('theater_references', ['reference_code' => 'OLD1']);
        $this->assertDatabaseHas('theater_references', ['reference_code' => 'NEW1']);
    }

    public function test_it_predicts_unit_song_from_the_previous_show_with_the_same_setlist(): void
    {
        $this->setShortname('Oniel');

        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => '2026/01/01',
            'setlist' => 'Cara Meminum Ramune',
            'unit_song' => 'Nice to Meet You!',
        ]);

        $this->fakeTheater([
            $this->show(['reference_code' => 'NEWREF', 'title' => 'Cara Meminum Ramune', 'date' => '2026-09-13']),
        ]);

        $this->artisan('app:fetch-theater-shows')->assertExitCode(0);

        $this->assertDatabaseHas('show_teater', [
            'setlist' => 'Cara Meminum Ramune',
            'show_date' => '2026-09-13',
            'unit_song' => 'Nice to Meet You!',
        ]);
    }

    public function test_it_predicts_center_flags_from_the_previous_show_with_the_same_setlist(): void
    {
        $this->setShortname('Oniel');

        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => '2026/01/01',
            'setlist' => 'Cara Meminum Ramune',
            'unit_song' => 'Nice to Meet You!',
            'is_global_center' => null,
            'is_us_center' => 1,
        ]);

        $this->fakeTheater([
            $this->show(['reference_code' => 'CENTER1', 'title' => 'Cara Meminum Ramune', 'date' => '2026-09-13']),
        ]);

        $this->artisan('app:fetch-theater-shows')->assertExitCode(0);

        $this->assertDatabaseHas('show_teater', [
            'show_date' => '2026-09-13',
            'unit_song' => 'Nice to Meet You!',
            'is_us_center' => 1,
        ]);

        // Center lain dibiarkan NULL (bukan 0) agar statistik IS NOT NULL tetap benar.
        $this->assertNull(DB::table('show_teater')->where('show_date', '2026-09-13')->value('is_global_center'));
    }

    public function test_it_backfills_center_on_an_already_synced_show(): void
    {
        $this->setShortname('Oniel');

        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => '2026/01/01',
            'setlist' => 'Cara Meminum Ramune',
            'unit_song' => 'Nice to Meet You!',
            'is_us_center' => 1,
        ]);

        TheaterReference::query()->create([
            'reference_code' => 'EXIST1',
            'month' => Timezone::nowLocal()->month,
            'year' => Timezone::nowLocal()->year,
            'processed_at' => now(),
        ]);

        // Show target sudah ada, reference sudah tercatat, center masih NULL.
        DB::table('show_teater')->insert([
            'show_id' => 2,
            'show_date' => '2026/09/13',
            'setlist' => 'Cara Meminum Ramune',
            'is_member_show' => 1,
        ]);

        $this->fakeTheater([
            $this->show(['reference_code' => 'EXIST1', 'title' => 'Cara Meminum Ramune', 'date' => '2026-09-13']),
        ]);

        $this->artisan('app:fetch-theater-shows')->assertExitCode(0);

        $this->assertSame(1, (int) DB::table('show_teater')->where('show_id', 2)->value('is_us_center'));
    }

    public function test_it_does_not_predict_unit_song_when_predictor_is_disabled(): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => 'show_teater_predictor_enabled'],
            ['value' => 'false', 'updated_at' => now()],
        );
        Cache::forget('app_settings');

        $this->setShortname('Oniel');

        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => '2026/01/01',
            'setlist' => 'Cara Meminum Ramune',
            'unit_song' => 'Nice to Meet You!',
        ]);

        $this->fakeTheater([
            $this->show(['reference_code' => 'NOPRED', 'title' => 'Cara Meminum Ramune', 'date' => '2026-09-13']),
        ]);

        $this->artisan('app:fetch-theater-shows')->assertExitCode(0);

        $this->assertDatabaseHas('show_teater', [
            'show_date' => '2026-09-13',
            'unit_song' => null,
        ]);
    }

    public function test_it_reports_already_synced_when_reference_and_show_already_exist(): void
    {
        $this->setShortname('Oniel');

        TheaterReference::query()->create([
            'reference_code' => 'SHD44C',
            'month' => now()->month,
            'year' => now()->year,
            'processed_at' => now(),
        ]);

        $nextShowId = (int) (DB::table('show_teater')->max('show_id') ?? 0) + 1;
        DB::table('show_teater')->insert([
            'show_id' => $nextShowId,
            'show_date' => '2026/09/13',
            'setlist' => 'Cara Meminum Ramune',
            'is_member_show' => 1,
        ]);

        $this->fakeTheater([
            $this->show(['reference_code' => 'SHD44C', 'title' => 'Cara Meminum Ramune', 'date' => '2026-09-13']),
        ]);

        $countBefore = DB::table('show_teater')->count();

        $this->artisan('app:fetch-theater-shows')
            ->expectsOutputToContain('Show terbaru sudah tersinkronisasi')
            ->assertExitCode(0);

        $this->assertSame($countBefore, DB::table('show_teater')->count());
    }

    public function test_fetch_manually_endpoint_returns_already_synced_message(): void
    {
        $this->actingAs(User::factory()->create());
        $this->setShortname('Oniel');

        TheaterReference::query()->create([
            'reference_code' => 'SHD44C',
            'month' => now()->month,
            'year' => now()->year,
            'processed_at' => now(),
        ]);

        $nextShowId = (int) (DB::table('show_teater')->max('show_id') ?? 0) + 1;
        DB::table('show_teater')->insert([
            'show_id' => $nextShowId,
            'show_date' => '2026/09/13',
            'setlist' => 'Cara Meminum Ramune',
            'is_member_show' => 1,
        ]);

        $this->fakeTheater([
            $this->show(['reference_code' => 'SHD44C', 'title' => 'Cara Meminum Ramune', 'date' => '2026-09-13']),
        ]);

        $response = $this->post(route('show-teater.fetch-manually'));

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertStringContainsString('Show terbaru sudah tersinkronisasi', (string) $response->json('message'));
    }

    public function test_about_settings_get_helper_fetches_value_by_key(): void
    {
        DB::table('about_settings')->updateOrInsert(
            ['key' => 'idol_name'],
            ['value' => 'Freya Jayawardana', 'updated_at' => now()]
        );

        $this->assertSame('Freya Jayawardana', AboutSettings::get('idol_name'));
        $this->assertSame('Default Value', AboutSettings::get('non_existent_key', 'Default Value'));
    }

    private function setShortname(string $value): void
    {
        DB::table('about_settings')->updateOrInsert(
            ['key' => 'idol_shortname'],
            ['value' => $value, 'updated_at' => now()]
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $shows
     */
    private function fakeTheater(array $shows): void
    {
        Http::fake([
            'https://jkt48connect.test/api/v1/theater*' => Http::response([
                'ok' => true,
                'data' => $shows,
            ], 200),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function show(array $overrides = []): array
    {
        return array_merge([
            'date' => '2026-09-13',
            'type' => 'SHOW',
            'title' => 'Cara Meminum Ramune',
            'reference_code' => 'REF-001',
            'lineup' => [
                ['name' => 'Aralie'],
                ['name' => 'Oniel'],
            ],
        ], $overrides);
    }
}
