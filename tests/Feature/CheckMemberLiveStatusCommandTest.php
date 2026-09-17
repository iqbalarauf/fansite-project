<?php

namespace Tests\Feature;

use App\Models\LiveStreaming;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckMemberLiveStatusCommandTest extends TestCase
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

    public function test_it_stores_new_live_sessions_for_the_idol(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            ['name' => 'Oniel JKT48', 'platform' => 'showroom', 'url_key' => 'jkt48_oniel', 'slug' => 'oniel-showroom-1', 'started_at' => '2026-09-16T22:53:50+07:00', 'title' => null],
            ['name' => 'Lulu JKT48', 'platform' => 'idn', 'slug' => 'lulu-idn-1', 'started_at' => '2026-09-16T22:00:00+07:00'],
        ]);

        $this->artisan('app:check-member-live')->assertExitCode(0);

        $this->assertDatabaseHas('live_streaming', [
            'live_id' => 'oniel-showroom-1',
            'platform' => 'Showroom',
            'live_date' => '2026-09-16',
        ]);

        $this->assertDatabaseMissing('live_streaming', ['live_id' => 'lulu-idn-1']);
    }

    public function test_it_stores_idn_sessions_with_additional_info(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            ['name' => 'Oniel JKT48', 'platform' => 'idn', 'url_key' => 'jkt48_oniel', 'slug' => 'oniel-idn-1', 'started_at' => '2026-09-16T23:10:00+07:00', 'title' => 'Ngobrol santai'],
        ]);

        $this->artisan('app:check-member-live')->assertExitCode(0);

        $this->assertDatabaseHas('live_streaming', [
            'live_id' => 'oniel-idn-1',
            'platform' => 'IDN App',
            'additional_info' => 'Ngobrol santai',
        ]);
    }

    public function test_it_does_not_duplicate_existing_live_sessions(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            ['name' => 'Oniel JKT48', 'platform' => 'showroom', 'slug' => 'oniel-showroom-1', 'started_at' => '2026-09-16T22:53:50+07:00'],
        ]);

        $this->artisan('app:check-member-live')->assertExitCode(0);
        $this->artisan('app:check-member-live')->assertExitCode(0);

        $this->assertSame(1, LiveStreaming::query()->where('live_id', 'oniel-showroom-1')->count());
    }

    public function test_it_skips_items_with_unknown_platform(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            ['name' => 'Oniel JKT48', 'platform' => 'unknown', 'slug' => 'oniel-unknown-1', 'started_at' => '2026-09-16T22:53:50+07:00'],
        ]);

        $this->artisan('app:check-member-live')->assertExitCode(0);

        $this->assertDatabaseMissing('live_streaming', ['live_id' => 'oniel-unknown-1']);
    }

    public function test_it_fails_when_idol_shortname_is_missing(): void
    {
        DB::table('about_settings')->where('key', 'idol_shortname')->delete();

        $this->artisan('app:check-member-live')->assertExitCode(1);
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
