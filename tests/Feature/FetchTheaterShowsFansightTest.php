<?php

namespace Tests\Feature;

use App\Models\ShowTeater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchTheaterShowsFansightTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.jkt48connect.url' => 'https://jkt48connect.test',
            'services.jkt48connect.key' => 'test-key',
            'services.fansight.url' => 'https://fansight.test',
        ]);
    }

    public function test_it_fails_when_the_fansight_api_is_not_configured(): void
    {
        config(['services.fansight.url' => '']);

        $this->artisan('app:fetch-theater-shows', ['--source' => 'fansight'])
            ->expectsOutput('FANSIGHT API is not configured (API_FANSIGHT_URL).')
            ->assertFailed();
    }

    public function test_it_fails_when_idol_name_is_missing(): void
    {
        DB::table('about_settings')->where('key', 'idol_name')->delete();

        $this->artisan('app:fetch-theater-shows', ['--source' => 'fansight'])
            ->expectsOutput('Idol name not found in about_settings.')
            ->assertFailed();
    }

    public function test_it_url_encodes_the_idol_name_with_spaces(): void
    {
        $this->setIdolName('Cornelia Vanisa');
        $this->fakeFansight([]);

        $this->artisan('app:fetch-theater-shows', ['--source' => 'fansight'])->assertExitCode(0);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'idol_name=Cornelia%20Vanisa'));
    }

    public function test_it_saves_shows_from_the_fansight_api(): void
    {
        $this->setIdolName('Cornelia Vanisa');

        $this->fakeFansight([
            $this->show([
                'reference_code' => 'SHAB0F',
                'date' => '2026-10-09',
                'title' => 'Pertaruhan Cinta',
            ]),
            $this->show([
                'reference_code' => 'SH4A49',
                'date' => '2026-10-11',
                'title' => 'PASSION 200%',
            ]),
        ]);

        $this->artisan('app:fetch-theater-shows', ['--source' => 'fansight'])
            ->expectsOutputToContain('Pertaruhan Cinta')
            ->expectsOutput('Fetch completed.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('show_teater', [
            'setlist' => 'Pertaruhan Cinta',
            'show_date' => '2026-10-09',
            'reference_code' => 'SHAB0F',
            'is_scraped_data' => 1,
        ]);

        $this->assertDatabaseHas('show_teater', [
            'setlist' => 'PASSION 200%',
            'show_date' => '2026-10-11',
            'reference_code' => 'SH4A49',
        ]);

        $this->assertDatabaseHas('theater_references', ['reference_code' => 'SHAB0F']);
        $this->assertDatabaseHas('theater_references', ['reference_code' => 'SH4A49']);
    }

    public function test_it_skips_non_show_types(): void
    {
        $this->setIdolName('Cornelia Vanisa');

        $this->fakeFansight([
            $this->show(['reference_code' => 'EV1234', 'type' => 'EVENT']),
        ]);

        $this->artisan('app:fetch-theater-shows', ['--source' => 'fansight'])->assertExitCode(0);

        $this->assertDatabaseMissing('show_teater', ['reference_code' => 'EV1234']);
    }

    public function test_it_skips_shows_without_matching_lineup(): void
    {
        $this->setIdolName('Cornelia Vanisa');

        $this->fakeFansight([
            $this->show([
                'reference_code' => 'SH9999',
                'matched_members' => [],
                'members' => [['name' => 'Aralie'], ['name' => 'Christy']],
            ]),
        ]);

        $this->artisan('app:fetch-theater-shows', ['--source' => 'fansight'])->assertExitCode(0);

        $this->assertDatabaseMissing('show_teater', ['reference_code' => 'SH9999']);
    }

    public function test_it_does_not_duplicate_an_already_synced_show(): void
    {
        $this->setIdolName('Cornelia Vanisa');

        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => '2026-10-09',
            'setlist' => 'Pertaruhan Cinta',
            'is_member_show' => 1,
        ]);

        $this->fakeFansight([
            $this->show(['reference_code' => 'SHAB0F', 'date' => '2026-10-09', 'title' => 'Pertaruhan Cinta']),
        ]);

        $this->artisan('app:fetch-theater-shows', ['--source' => 'fansight'])
            ->expectsOutputToContain('Show terbaru sudah tersinkronisasi')
            ->assertExitCode(0);

        $this->assertSame(1, DB::table('show_teater')->count());
        $this->assertSame('SHAB0F', ShowTeater::query()->where('show_id', 1)->value('reference_code'));
    }

    public function test_fetch_manually_endpoint_uses_the_fansight_source(): void
    {
        $this->actingAs(User::factory()->create());
        $this->setIdolName('Cornelia Vanisa');

        $this->fakeFansight([
            $this->show(['reference_code' => 'SHAB0F', 'date' => '2026-10-09', 'title' => 'Pertaruhan Cinta']),
        ]);

        $response = $this->post(route('show-teater.fetch-manually'), ['source' => 'fansight']);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('show_teater', ['reference_code' => 'SHAB0F']);
    }

    public function test_fetch_manually_endpoint_rejects_an_invalid_source(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('show-teater.fetch-manually'), ['source' => 'invalid'])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    private function setIdolName(string $value): void
    {
        DB::table('about_settings')->updateOrInsert(
            ['key' => 'idol_name'],
            ['value' => $value, 'updated_at' => now()],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $shows
     */
    private function fakeFansight(array $shows): void
    {
        Http::fake([
            'https://fansight.test/shows*' => Http::response([
                'status' => true,
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
            'schedule_id' => 7342,
            'date' => '2026-10-09',
            'type' => 'SHOW',
            'title' => 'Pertaruhan Cinta',
            'reference_code' => 'SHAB0F',
            'members' => [['name' => 'Cornelia Vanisa', 'member_id' => 59]],
            'matched_members' => [['name' => 'Cornelia Vanisa', 'member_id' => 59]],
        ], $overrides);
    }
}
