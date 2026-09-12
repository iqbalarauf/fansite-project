<?php

namespace Tests\Feature;

use App\Models\LiveStreaming;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchStreamingInfoTest extends TestCase
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

    public function test_it_fails_when_the_api_is_not_configured(): void
    {
        config(['services.jkt48connect.url' => '', 'services.jkt48connect.key' => '']);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutput('JKT48Connect API is not configured (JKT48CONNECT_LIVE_URL / JKT48CONNECT_API_KEY).')
            ->assertFailed();
    }

    public function test_it_outputs_error_if_idol_shortname_is_missing(): void
    {
        DB::table('about_settings')->where('key', 'idol_shortname')->delete();

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutput('Idol shortname not found in about_settings.')
            ->assertFailed();
    }

    public function test_it_saves_an_idn_live_for_the_matched_member(): void
    {
        $this->setShortname('Oniel');
        $this->fakeRecent([$this->item()]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutputToContain('1 data live streaming ditambahkan.')
            ->expectsOutput('Fetch completed.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('live_streaming', [
            'live_id' => 'diroriiiii-260912105040',
            'platform' => 'IDN App',
            'live_date' => '2026-09-12',
            'duration' => 60,
            'additional_info' => 'Halo',
        ]);
    }

    public function test_it_saves_a_showroom_live_for_the_matched_member(): void
    {
        $this->setShortname('Oniel');
        $this->fakeRecent([
            $this->item(['_id' => 'sr-260912105040', 'type' => 'showroom']),
        ]);

        $this->artisan('app:fetch-streaming-info')->assertExitCode(0);

        $this->assertDatabaseHas('live_streaming', [
            'live_id' => 'sr-260912105040',
            'platform' => 'Showroom',
        ]);
    }

    public function test_it_skips_lives_of_other_members(): void
    {
        $this->setShortname('Oniel');
        $this->fakeRecent([
            $this->item(['member' => ['name' => 'Jessi JKT48']]),
        ]);

        $this->artisan('app:fetch-streaming-info')->assertExitCode(0);

        $this->assertSame(0, DB::table('live_streaming')->count());
    }

    public function test_it_reports_when_no_live_is_found_for_the_member(): void
    {
        $this->setShortname('Oniel');
        $this->fakeRecent([
            $this->item(['member' => ['name' => 'Jessi JKT48']]),
        ]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutput('Tidak ada live streaming dari Oniel JKT48 hari ini')
            ->assertExitCode(0);
    }

    public function test_it_reports_when_there_is_no_streaming_data(): void
    {
        $this->setShortname('Oniel');
        $this->fakeRecent([]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutput('Tidak ada live streaming dari Oniel JKT48 hari ini')
            ->assertExitCode(0);
    }

    public function test_it_reports_when_the_live_is_already_saved(): void
    {
        $this->setShortname('Oniel');

        LiveStreaming::query()->create([
            'live_id' => 'diroriiiii-260912105040',
            'platform' => 'IDN App',
            'live_date' => '2026-09-12',
            'duration' => 60,
        ]);

        $this->fakeRecent([$this->item()]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutputToContain('Data Live sudah tersimpan sebelumnya')
            ->assertExitCode(0);

        $this->assertSame(1, DB::table('live_streaming')->count());
    }

    public function test_fetch_manually_endpoint_returns_the_command_output(): void
    {
        $this->actingAs(User::factory()->create());
        $this->setShortname('Oniel');
        $this->fakeRecent([$this->item()]);

        $response = $this->post(route('live-streaming.fetch-manually'));

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertStringContainsString('1 data live streaming ditambahkan.', (string) $response->json('message'));
    }

    private function setShortname(string $value): void
    {
        DB::table('about_settings')->updateOrInsert(
            ['key' => 'idol_shortname'],
            ['value' => $value, 'updated_at' => now()],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function fakeRecent(array $items): void
    {
        Http::fake([
            'https://jkt48connect.test/api/v1/recent*' => Http::response([
                'ok' => true,
                'data' => $items,
            ], 200),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function item(array $overrides = []): array
    {
        return array_merge([
            '_id' => 'diroriiiii-260912105040',
            'type' => 'idn',
            'member' => ['name' => 'Oniel JKT48'],
            'idn' => ['title' => 'Halo'],
            'live_info' => [
                'duration' => 3600000,
                'date' => ['start' => '2026-09-12T03:50:47.000Z'],
            ],
        ], $overrides);
    }
}
