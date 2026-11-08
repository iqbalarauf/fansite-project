<?php

namespace Tests\Feature;

use App\Support\CheckMemberLive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckMemberLiveTest extends TestCase
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

    public function test_it_detects_showroom_live(): void
    {
        $this->fakeLive([
            ['name' => 'Jesslyn', 'type' => 'showroom', 'url_key' => 'jkt48_jesslyn'],
            ['name' => 'Oniel', 'type' => 'showroom', 'url_key' => 'jkt48_oniel'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertTrue($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_it_detects_idn_live(): void
    {
        $this->fakeLive([
            ['name' => 'Oniel', 'type' => 'idn', 'url_key' => 'jkt48_oniel'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertFalse($status['showroom']);
        $this->assertTrue($status['idn']);
    }

    public function test_it_returns_offline_when_the_member_is_not_found(): void
    {
        $this->fakeLive([
            ['name' => 'Jesslyn', 'type' => 'showroom', 'url_key' => 'jkt48_jesslyn'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertFalse($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_it_returns_offline_when_the_api_fails(): void
    {
        Http::fake([
            'https://jkt48connect.test/api/now-live*' => Http::response([], 500),
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertFalse($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_welcome_page_shows_online_badge_when_the_idol_is_live(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            ['name' => 'Oniel', 'type' => 'showroom', 'url_key' => 'jkt48_oniel'],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Showroom Live')
            ->assertSee('Online');
    }

    public function test_welcome_page_shows_offline_when_the_idol_is_not_live(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Showroom Live')
            ->assertSee('Offline')
            ->assertDontSee('Online');
    }

    public function test_matching_uses_idol_shortname_only(): void
    {
        DB::table('about_settings')->updateOrInsert(
            ['key' => 'idol_name'],
            ['value' => 'Cornelia Vanisa', 'updated_at' => now()],
        );
        DB::table('about_settings')->where('key', 'idol_shortname')->delete();

        $this->fakeLive([
            ['name' => 'Cornelia Vanisa', 'type' => 'showroom', 'url_key' => 'jkt48_cornelia'],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Offline')
            ->assertDontSee('Online');
    }

    public function test_it_matches_url_key_case_insensitively(): void
    {
        $this->fakeLive([
            ['name' => 'Nayla Suji', 'type' => 'showroom', 'url_key' => 'JKT48_Nayla'],
        ]);

        $status = app(CheckMemberLive::class)->status('Nayla');

        $this->assertTrue($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_it_matches_by_url_key_even_when_the_name_differs(): void
    {
        $this->fakeLive([
            ['name' => 'Cornelia Vanisa', 'url_key' => 'jkt48_oniel', 'type' => 'idn'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertFalse($status['showroom']);
        $this->assertTrue($status['idn']);
    }

    public function test_it_does_not_match_by_name_when_url_key_is_present_and_differs(): void
    {
        $this->fakeLive([
            ['name' => 'Oniel JKT48', 'url_key' => 'jkt48_jesslyn', 'type' => 'idn'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertFalse($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_it_falls_back_to_the_name_when_url_key_is_missing(): void
    {
        $this->fakeLive([
            ['name' => 'Oniel JKT48', 'type' => 'showroom'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertTrue($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_it_uses_the_type_field_to_determine_the_platform(): void
    {
        $this->fakeLive([
            ['name' => 'Oniel', 'type' => 'showroom', 'url_key' => 'jkt48_oniel'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertTrue($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_welcome_page_shows_the_live_member_name_and_image(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            [
                'name' => 'Live Oniel 123',
                'img' => 'https://cdn.example/oniel-live.webp',
                'type' => 'idn',
                'url_key' => 'jkt48_oniel',
                'streaming_url_list' => [
                    ['label' => 'original', 'quality' => 1, 'url' => 'https://stream.example/oniel.m3u8'],
                ],
            ],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('IDN App')
            ->assertSee('Online')
            ->assertSee('Live Oniel 123')
            ->assertSee('https://cdn.example/oniel-live.webp', false);
    }

    public function test_details_returns_live_state_and_stream_url_per_platform(): void
    {
        $this->fakeLive([
            [
                'name' => 'Oniel',
                'img' => 'https://cdn.example/oniel.webp',
                'type' => 'idn',
                'url_key' => 'jkt48_oniel',
                'streaming_url_list' => [
                    ['label' => 'original', 'quality' => 1, 'url' => 'https://stream.example/oniel.m3u8'],
                    ['label' => 'low', 'quality' => 100, 'url' => 'https://stream.example/oniel-low.m3u8'],
                ],
            ],
        ]);

        $details = app(CheckMemberLive::class)->details('Oniel');

        $this->assertTrue($details['idn']['live']);
        $this->assertSame('https://stream.example/oniel.m3u8', $details['idn']['url']);
        $this->assertSame('Oniel', $details['idn']['name']);
        $this->assertSame('https://cdn.example/oniel.webp', $details['idn']['image']);
        $this->assertFalse($details['showroom']['live']);
        $this->assertNull($details['showroom']['url']);
        $this->assertNull($details['showroom']['name']);
        $this->assertNull($details['showroom']['image']);
    }

    public function test_details_skips_duplicate_idn_entries_with_an_empty_stream_list(): void
    {
        $this->fakeLive([
            [
                'name' => 'Eli',
                'img' => 'https://cdn.example/eli-live.webp',
                'type' => 'idn',
                'url_key' => 'jkt48_eli',
                'streaming_url_list' => [
                    ['label' => 'original', 'quality' => 1, 'url' => 'https://stream.example/eli.m3u8'],
                ],
            ],
            [
                'name' => 'Helisma Putri',
                'img' => 'https://cdn.example/eli-member.jpg',
                'type' => 'idn',
                'url_key' => 'jkt48_eli',
                'streaming_url_list' => [],
            ],
        ]);

        $details = app(CheckMemberLive::class)->details('Eli');

        $this->assertTrue($details['idn']['live']);
        $this->assertSame('https://stream.example/eli.m3u8', $details['idn']['url']);
        $this->assertSame('Eli', $details['idn']['name']);
        $this->assertSame('https://cdn.example/eli-live.webp', $details['idn']['image']);
    }

    public function test_details_does_not_overwrite_an_existing_url_with_an_empty_list(): void
    {
        $this->fakeLive([
            [
                'name' => 'Mikaela',
                'type' => 'idn',
                'url_key' => 'jkt48_mikaela',
                'streaming_url_list' => [],
            ],
            [
                'name' => 'Mikaela Kusjanto',
                'type' => 'idn',
                'url_key' => 'jkt48_mikaela',
                'streaming_url_list' => [
                    ['label' => 'original', 'quality' => 1, 'url' => 'https://stream.example/mikaela.m3u8'],
                ],
            ],
        ]);

        $details = app(CheckMemberLive::class)->details('Mikaela');

        $this->assertTrue($details['idn']['live']);
        $this->assertSame('https://stream.example/mikaela.m3u8', $details['idn']['url']);
    }

    public function test_details_falls_back_to_the_legacy_streaming_field(): void
    {
        $this->fakeLive([
            ['name' => 'Oniel JKT48', 'type' => 'showroom', 'streaming' => [['url' => 'https://stream.example/showroom.m3u8']]],
        ]);

        $details = app(CheckMemberLive::class)->details('Oniel');

        $this->assertTrue($details['showroom']['live']);
        $this->assertSame('https://stream.example/showroom.m3u8', $details['showroom']['url']);
    }

    public function test_details_falls_back_to_the_legacy_stream_url_field(): void
    {
        $this->fakeLive([
            ['name' => 'Oniel', 'type' => 'idn', 'url_key' => 'jkt48_oniel', 'stream_url' => 'https://stream.example/live-oniel'],
        ]);

        $details = app(CheckMemberLive::class)->details('Oniel');

        $this->assertTrue($details['idn']['live']);
        $this->assertSame('https://stream.example/live-oniel', $details['idn']['url']);
    }

    public function test_welcome_page_links_to_stream_url_and_shows_the_platform_icon(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            [
                'name' => 'Oniel',
                'img' => 'https://cdn.example/oniel-live.webp',
                'type' => 'idn',
                'url_key' => 'jkt48_oniel',
                'streaming_url_list' => [
                    ['label' => 'original', 'quality' => 1, 'url' => 'https://stream.example/live-oniel'],
                ],
            ],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('IDN App')
            ->assertSee('Online')
            ->assertSee('href="https://stream.example/live-oniel"', false)
            ->assertSee('icon-app/idn.webp', false)
            ->assertSee('Tonton sekarang');
    }

    public function test_welcome_page_does_not_link_when_offline(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Offline')
            ->assertDontSee('Tonton sekarang');
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
    private function fakeLive(array $items): void
    {
        Http::fake([
            'https://jkt48connect.test/api/now-live*' => Http::response($items, 200),
        ]);
    }
}
