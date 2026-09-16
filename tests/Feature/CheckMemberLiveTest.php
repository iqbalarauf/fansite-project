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
            ['name' => 'Jesslyn', 'platform' => 'showroom'],
            ['name' => 'Oniel', 'platform' => 'showroom'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertTrue($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_it_detects_idn_live(): void
    {
        $this->fakeLive([
            ['name' => 'Oniel', 'platform' => 'idn'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertFalse($status['showroom']);
        $this->assertTrue($status['idn']);
    }

    public function test_it_returns_offline_when_the_member_is_not_found(): void
    {
        $this->fakeLive([
            ['name' => 'Jesslyn', 'platform' => 'showroom'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertFalse($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_it_returns_offline_when_the_api_fails(): void
    {
        Http::fake([
            'https://jkt48connect.test/api/v1/live*' => Http::response([], 500),
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertFalse($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_welcome_page_shows_online_badge_when_the_idol_is_live(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            ['name' => 'Oniel', 'platform' => 'showroom'],
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
            ['name' => 'Cornelia Vanisa', 'platform' => 'showroom'],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Offline')
            ->assertDontSee('Online');
    }

    public function test_it_matches_names_with_the_jkt48_suffix(): void
    {
        $this->fakeLive([
            ['name' => 'Oniel JKT48', 'platform' => 'idn', 'type' => 'idn', 'url_key' => 'jkt48_oniel'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertFalse($status['showroom']);
        $this->assertTrue($status['idn']);
    }

    public function test_it_matches_by_url_key_when_available(): void
    {
        $this->fakeLive([
            ['name' => 'Cornelia Vanisa JKT48', 'url_key' => 'jkt48_oniel', 'type' => 'idn'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertFalse($status['showroom']);
        $this->assertTrue($status['idn']);
    }

    public function test_it_falls_back_to_the_type_field_when_platform_is_missing(): void
    {
        $this->fakeLive([
            ['name' => 'Oniel JKT48', 'type' => 'showroom'],
        ]);

        $status = app(CheckMemberLive::class)->status('Oniel');

        $this->assertTrue($status['showroom']);
        $this->assertFalse($status['idn']);
    }

    public function test_welcome_page_shows_online_badge_with_the_real_api_name_format(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            ['name' => 'Oniel JKT48', 'platform' => 'idn', 'type' => 'idn', 'url_key' => 'jkt48_oniel'],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('IDN App')
            ->assertSee('Online');
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
            'https://jkt48connect.test/api/v1/live*' => Http::response([
                'ok' => true,
                'data' => $items,
            ], 200),
        ]);
    }
}
