<?php

namespace Tests\Feature;

use App\Models\AboutSettings;
use App\Models\TheaterReference;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchTheaterShowsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_outputs_error_if_idol_name_is_missing(): void
    {
        DB::table('about_settings')->where('key', 'idol_name')->delete();

        $this->artisan('app:fetch-theater-shows')
            ->expectsOutput('Idol name not found in about_settings.')
            ->assertFailed();
    }

    public function test_it_fetches_and_saves_theater_shows_when_idol_name_matches(): void
    {
        DB::table('about_settings')->updateOrInsert(
            ['key' => 'idol_name'],
            ['value' => 'Freya Jayawardana', 'updated_at' => now()]
        );

        $currentMonth = now()->month;
        $currentYear = now()->year;

        Http::fake([
            "https://jkt48.com/api/v1/schedules?lang=id&month={$currentMonth}&year={$currentYear}&type=SHOW" => Http::response([
                'data' => [
                    ['reference_code' => 'test-show-ref-001'],
                ],
            ], 200),
            'https://jkt48.com/api/v1/theater-shows/test-show-ref-001?lang=id' => Http::response([
                'data' => [
                    'date' => '2026-09-10 12:00:00',
                    'title' => 'Cara Meminum Ramune',
                    'jkt48_member' => [
                        ['name' => 'Freya Jayawardana'],
                        ['name' => 'Member Lain'],
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('app:fetch-theater-shows')
            ->expectsOutputToContain('Cara Meminum Ramune')
            ->expectsOutput('Fetch completed.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('show_teater', [
            'setlist' => 'Cara Meminum Ramune',
            'show_date' => '2026/09/10',
            'is_scraped_data' => 1,
        ]);

        $this->assertDatabaseHas('theater_references', [
            'reference_code' => 'test-show-ref-001',
            'month' => $currentMonth,
            'year' => $currentYear,
        ]);

        $ref = TheaterReference::where('reference_code', 'test-show-ref-001')->first();
        $this->assertNotNull($ref->processed_at);
    }

    public function test_it_skips_inserting_show_when_date_and_setlist_already_exist(): void
    {
        DB::table('about_settings')->updateOrInsert(
            ['key' => 'idol_name'],
            ['value' => 'Freya Jayawardana', 'updated_at' => now()]
        );

        $nextShowId = (int) (DB::table('show_teater')->max('show_id') ?? 0) + 1;
        DB::table('show_teater')->insert([
            'show_id' => $nextShowId,
            'show_date' => '2026/09/10',
            'setlist' => 'Cara Meminum Ramune',
            'is_member_show' => 1,
        ]);

        $currentMonth = now()->month;
        $currentYear = now()->year;

        Http::fake([
            "https://jkt48.com/api/v1/schedules?lang=id&month={$currentMonth}&year={$currentYear}&type=SHOW" => Http::response([
                'data' => [
                    ['reference_code' => 'test-show-ref-002'],
                ],
            ], 200),
            'https://jkt48.com/api/v1/theater-shows/test-show-ref-002?lang=id' => Http::response([
                'data' => [
                    'date' => '2026-09-10 12:00:00',
                    'title' => 'Cara Meminum Ramune',
                    'jkt48_member' => [
                        ['name' => 'Freya Jayawardana'],
                    ],
                ],
            ], 200),
        ]);

        $countBefore = DB::table('show_teater')->count();

        $this->artisan('app:fetch-theater-shows')
            ->expectsOutputToContain('Show already exists')
            ->expectsOutput('Fetch completed.')
            ->assertExitCode(0);

        $this->assertSame($countBefore, DB::table('show_teater')->count());
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
}
