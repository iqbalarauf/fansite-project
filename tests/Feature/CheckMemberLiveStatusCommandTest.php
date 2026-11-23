<?php

namespace Tests\Feature;

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

    public function test_it_refreshes_live_sessions_without_storing_them(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            ['name' => 'Oniel', 'type' => 'showroom', 'url_key' => 'jkt48_oniel', 'slug' => 'oniel-showroom-1', 'started_at' => '2026-09-16T22:53:50+07:00'],
            ['name' => 'Lulu', 'type' => 'idn', 'url_key' => 'jkt48_lulu', 'slug' => 'lulu-idn-1', 'started_at' => '2026-09-16T22:00:00+07:00'],
        ]);

        $this->artisan('app:check-member-live')->assertExitCode(0);

        Http::assertSentCount(1);
        $this->assertDatabaseCount('live_streaming', 0);
    }

    public function test_it_does_not_persist_the_notification_caption_to_live_streaming(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            [
                'name' => 'Oniel',
                'type' => 'idn',
                'url_key' => 'jkt48_oniel',
                'slug' => 'oniel-idn-1',
                'started_at' => '2026-09-16T23:10:00+07:00',
                'notification_caption' => '🔴 Oniel — Ngobrol santai',
            ],
        ]);

        $this->artisan('app:check-member-live')->assertExitCode(0);

        $this->assertDatabaseMissing('live_streaming', ['live_id' => 'oniel-idn-1']);
        $this->assertDatabaseCount('live_streaming', 0);
    }

    public function test_it_does_not_create_rows_when_run_repeatedly(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            ['name' => 'Oniel', 'type' => 'showroom', 'url_key' => 'jkt48_oniel', 'slug' => 'oniel-showroom-1', 'started_at' => '2026-09-16T22:53:50+07:00'],
        ]);

        $this->artisan('app:check-member-live')->assertExitCode(0);
        $this->artisan('app:check-member-live')->assertExitCode(0);

        $this->assertDatabaseCount('live_streaming', 0);
    }

    public function test_it_ignores_items_with_unknown_platform(): void
    {
        $this->setShortname('Oniel');
        $this->fakeLive([
            ['name' => 'Oniel', 'type' => 'unknown', 'url_key' => 'jkt48_oniel', 'slug' => 'oniel-unknown-1', 'started_at' => '2026-09-16T22:53:50+07:00'],
        ]);

        $this->artisan('app:check-member-live')->assertExitCode(0);

        $this->assertDatabaseMissing('live_streaming', ['live_id' => 'oniel-unknown-1']);
        $this->assertDatabaseCount('live_streaming', 0);
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
            'https://jkt48connect.test/api/now_live*' => Http::response($items, 200),
        ]);
    }
}
