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

    public function test_it_saves_an_idn_live_with_detail_data(): void
    {
        $this->setShortname('Oniel');
        $this->fakeDetail([$this->item()]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutputToContain('1 data live streaming ditambahkan')
            ->expectsOutput('Fetch completed.')
            ->assertExitCode(0);

        $live = LiveStreaming::query()->where('live_id', 'saya-kembali-260924180957')->firstOrFail();

        $this->assertSame('IDN App', $live->platform);
        $this->assertSame('2026-09-24', $live->live_date->toDateString());
        $this->assertSame(62, $live->duration);
        $this->assertSame(10244, $live->max_viewers);
        $this->assertSame(3155, $live->comment_count);
        $this->assertSame(79, $live->gift_count);
        $this->assertSame(1038, $live->total_gold);
        $this->assertSame('saya kembali', $live->additional_info);
        $this->assertSame('https://youtu.be/wgE7TF1tklk', $live->youtube_url);
        $this->assertCount(2, $live->gifts);
        $this->assertSame('Angklung', $live->gifts[0]['name']);
        $this->assertCount(2, $live->top_senders);
        $this->assertSame('Wetee .', $live->top_senders[0]['name']);
    }

    public function test_it_uses_the_name_query_and_detail_endpoint(): void
    {
        $this->setShortname('Oniel');
        $this->fakeDetail([$this->item()]);

        $this->artisan('app:fetch-streaming-info')->assertExitCode(0);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/recent/detail')
                && str_contains($request->url(), 'name=Oniel');
        });
    }

    public function test_it_saves_a_showroom_live(): void
    {
        $this->setShortname('Oniel');
        $this->fakeDetail([
            $this->item(['id' => 'sr-260912105040', 'type' => 'showroom']),
        ]);

        $this->artisan('app:fetch-streaming-info')->assertExitCode(0);

        $this->assertDatabaseHas('live_streaming', [
            'live_id' => 'sr-260912105040',
            'platform' => 'Showroom',
        ]);
    }

    public function test_it_skips_unknown_platforms(): void
    {
        $this->setShortname('Oniel');
        $this->fakeDetail([
            $this->item(['id' => 'unknown-1', 'type' => 'youtube']),
        ]);

        $this->artisan('app:fetch-streaming-info')->assertExitCode(0);

        $this->assertSame(0, DB::table('live_streaming')->count());
    }

    public function test_it_reports_when_there_is_no_streaming_data(): void
    {
        $this->setShortname('Oniel');
        $this->fakeDetail([]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutput('Tidak ada live streaming dari Oniel JKT48 hari ini')
            ->assertExitCode(0);
    }

    public function test_it_updates_an_existing_live_instead_of_duplicating(): void
    {
        $this->setShortname('Oniel');

        LiveStreaming::query()->create([
            'live_id' => 'saya-kembali-260924180957',
            'platform' => 'IDN App',
            'live_date' => '2026-09-24',
            'duration' => 10,
        ]);

        $this->fakeDetail([$this->item()]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutputToContain('1 diperbarui')
            ->assertExitCode(0);

        $this->assertSame(1, DB::table('live_streaming')->count());
        $this->assertSame(62, LiveStreaming::query()->firstOrFail()->duration);
    }

    public function test_fetch_manually_endpoint_returns_the_command_output(): void
    {
        $this->actingAs(User::factory()->create());
        $this->setShortname('Oniel');
        $this->fakeDetail([$this->item()]);

        $response = $this->post(route('live-streaming.fetch-manually'));

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertStringContainsString('1 data live streaming ditambahkan', (string) $response->json('message'));
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
    private function fakeDetail(array $items): void
    {
        Http::fake([
            'https://jkt48connect.test/api/v1/recent/detail*' => Http::response([
                'ok' => true,
                'data' => ['items' => $items],
            ], 200),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function item(array $overrides = []): array
    {
        // start 2026-09-24 18:09:57 WIB, end 2026-09-24 19:10:03 WIB.
        return array_merge([
            'id' => 'saya-kembali-260924180957',
            'name' => 'Oniel',
            'title' => 'saya kembali',
            'type' => 'idn',
            'start_time' => 1790248203000,
            'end_time' => 1790251935764,
            'max_viewers' => 10244,
            'comment_count' => 3155,
            'gift_count' => 79,
            'total_gold' => 1038,
            'youtube_url' => 'https://youtu.be/wgE7TF1tklk',
            'gifts_summary' => [
                ['slug' => 'angklung-1', 'name' => 'Angklung', 'image_url' => 'https://cdn.test/angklung.png', 'gold_per_unit' => 29, 'count' => 7, 'total_gold' => 203],
                ['slug' => 'panjat-1', 'name' => 'Panjat', 'image_url' => 'https://cdn.test/panjat.png', 'gold_per_unit' => 50, 'count' => 4, 'total_gold' => 200],
            ],
            'top_senders' => [
                ['uuid' => 'u1', 'name' => 'Wetee .', 'avatar' => 'https://cdn.test/wetee.webp', 'total_gold' => 220],
                ['uuid' => 'u2', 'name' => 'Laila Akrima -', 'avatar' => 'https://cdn.test/laila.webp', 'total_gold' => 187],
            ],
        ], $overrides);
    }
}
