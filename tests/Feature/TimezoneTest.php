<?php

namespace Tests\Feature;

use App\Models\NewsPost;
use App\Models\User;
use App\Support\Timezone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_helper_converts_local_to_utc(): void
    {
        $utc = Timezone::fromLocal('2026-09-21T20:00');

        $this->assertNotNull($utc);
        $this->assertSame('UTC', $utc->timezoneName);
        $this->assertSame('2026-09-21 13:00:00', $utc->toDateTimeString());
    }

    public function test_helper_converts_utc_to_local(): void
    {
        $local = Timezone::toLocal('2026-09-21 13:00:00');

        $this->assertNotNull($local);
        $this->assertSame('Asia/Jakarta', $local->timezoneName);
        $this->assertSame('2026-09-21 20:00:00', $local->toDateTimeString());
    }

    public function test_helper_handles_empty_values(): void
    {
        $this->assertNull(Timezone::fromLocal(null));
        $this->assertNull(Timezone::fromLocal(''));
        $this->assertNull(Timezone::toLocal(null));
        $this->assertNull(Timezone::toLocal(''));
    }

    public function test_published_at_is_stored_in_utc_from_local_input(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('content.news.store'), [
            'title' => 'Berita Terjadwal',
            'slug' => 'berita-terjadwal',
            'status' => 'published',
            'published_at' => '2026-09-21T20:00',
        ])->assertRedirect();

        $post = NewsPost::query()->firstOrFail();

        // 20:00 WIB (UTC+7) => 13:00 UTC
        $this->assertSame('2026-09-21 13:00:00', $post->published_at?->toDateTimeString());
    }
}
