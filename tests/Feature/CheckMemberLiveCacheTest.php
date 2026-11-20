<?php

namespace Tests\Feature;

use App\Support\CheckMemberLive;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckMemberLiveCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.jkt48connect.url' => 'https://jkt48connect.test',
            'services.jkt48connect.key' => 'test-key',
        ]);

        Http::fake([
            'https://jkt48connect.test/api/now_live*' => Http::response([
                ['name' => 'Oniel', 'type' => 'idn', 'url_key' => 'jkt48_oniel'],
            ], 200),
        ]);
    }

    public function test_now_live_payload_is_cached_between_scheduler_runs(): void
    {
        $memberLive = app(CheckMemberLive::class);
        $memberLive->status('Oniel');

        Http::assertSentCount(1);

        $this->travel(9)->minutes();
        $memberLive->status('Oniel');

        Http::assertSentCount(1);

        $this->travel(2)->minutes();
        $memberLive->status('Oniel');

        Http::assertSentCount(2);
    }

    public function test_forced_refresh_bypasses_the_shared_cache(): void
    {
        $memberLive = app(CheckMemberLive::class);

        $memberLive->matchingItems('Oniel');
        Http::assertSentCount(1);

        $memberLive->matchingItems('Oniel', refresh: true);
        Http::assertSentCount(2);
    }

    public function test_failed_forced_refresh_keeps_the_last_successful_payload(): void
    {
        Http::fakeSequence()
            ->push([
                ['name' => 'Oniel', 'type' => 'idn', 'url_key' => 'jkt48_oniel'],
            ], 200)
            ->push([], 500);

        $memberLive = app(CheckMemberLive::class);

        $this->assertTrue($memberLive->status('Oniel')['idn']);

        $this->travel(1)->minutes();
        $memberLive->matchingItems('Oniel', refresh: true);

        $this->assertTrue($memberLive->status('Oniel')['idn']);
        Http::assertSentCount(2);
    }
}
