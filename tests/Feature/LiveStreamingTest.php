<?php

namespace Tests\Feature;

use App\Models\LiveStreaming;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LiveStreamingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_open_live_streaming_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('live-streaming.index'));

        $response->assertOk();
        $response->assertSee('Live Streaming', false);
    }

    public function test_user_can_create_live_streaming(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('live-streaming.store'), [
            'platform' => 'Showroom',
            'live_date' => '2026-07-05',
            'duration' => 95,
            'additional_info' => 'Special anniversary stream',
        ]);

        $response->assertRedirect(route('live-streaming.index'));

        $this->assertDatabaseHas('live_streaming', [
            'platform' => 'Showroom',
            'live_date' => '2026-07-05',
            'duration' => 95,
            'additional_info' => 'Special anniversary stream',
        ]);
    }

    public function test_platform_is_required_for_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('live-streaming.store'), [
            'platform' => '',
            'live_date' => '2026-07-05',
            'duration' => 95,
        ]);

        $response->assertSessionHasErrors('platform');
    }

    public function test_user_can_update_live_streaming(): void
    {
        $user = User::factory()->create();

        $stream = LiveStreaming::create([
            'platform' => 'Showroom',
            'live_date' => '2026-07-10',
            'duration' => 60,
            'additional_info' => 'Initial info',
        ]);

        $response = $this->actingAs($user)->put(route('live-streaming.update', $stream), [
            'platform' => 'IDN App',
            'live_date' => '2026-07-11',
            'duration' => 75,
            'additional_info' => 'Updated info',
        ]);

        $response->assertRedirect(route('live-streaming.index'));

        $this->assertDatabaseHas('live_streaming', [
            'id' => $stream->id,
            'platform' => 'IDN App',
            'live_date' => '2026-07-11',
            'duration' => 75,
            'additional_info' => 'Updated info',
        ]);
    }

    public function test_live_id_is_unique(): void
    {
        LiveStreaming::create([
            'live_id' => 'duplicate-live-id',
            'platform' => 'Showroom',
            'live_date' => '2026-07-01',
        ]);

        $this->expectException(QueryException::class);

        LiveStreaming::create([
            'live_id' => 'duplicate-live-id',
            'platform' => 'IDN App',
            'live_date' => '2026-07-02',
        ]);
    }

    public function test_manual_creation_generates_a_unique_live_id(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('live-streaming.store'), [
            'platform' => 'Showroom',
            'live_date' => '2026-07-05',
            'duration' => 95,
        ])->assertRedirect(route('live-streaming.index'));

        $stream = LiveStreaming::query()->latest('id')->firstOrFail();

        $this->assertNotNull($stream->live_id);
        $this->assertStringStartsWith('manual-', (string) $stream->live_id);
    }

    public function test_manual_update_keeps_the_existing_live_id(): void
    {
        $user = User::factory()->create();

        $stream = LiveStreaming::create([
            'live_id' => 'keep-this-live-id',
            'platform' => 'Showroom',
            'live_date' => '2026-07-10',
        ]);

        $this->actingAs($user)->put(route('live-streaming.update', $stream), [
            'platform' => 'Showroom',
            'live_date' => '2026-07-12',
            'duration' => 70,
        ])->assertRedirect(route('live-streaming.index'));

        $this->assertSame('keep-this-live-id', $stream->fresh()->live_id);
    }

    public function test_live_id_is_backfilled_for_legacy_rows_on_update(): void
    {
        $user = User::factory()->create();

        $stream = LiveStreaming::create([
            'platform' => 'Showroom',
            'live_date' => '2026-07-10',
        ]);

        $this->assertNull($stream->live_id);

        $this->actingAs($user)->put(route('live-streaming.update', $stream), [
            'platform' => 'Showroom',
            'live_date' => '2026-07-12',
        ])->assertRedirect(route('live-streaming.index'));

        $liveId = (string) $stream->fresh()->live_id;

        $this->assertNotSame('', $liveId);
        $this->assertStringStartsWith('manual-', $liveId);
    }

    public function test_backfill_command_fills_missing_live_ids(): void
    {
        LiveStreaming::create(['platform' => 'Showroom', 'live_date' => '2026-01-01']);
        LiveStreaming::create(['platform' => 'IDN App', 'live_date' => '2026-01-02']);

        $this->artisan('app:backfill-live-ids')->assertExitCode(0);

        $this->assertSame(0, LiveStreaming::query()->whereNull('live_id')->count());

        // Running it again is a no-op.
        $this->artisan('app:backfill-live-ids')->assertExitCode(0);

        $this->assertSame(0, LiveStreaming::query()->whereNull('live_id')->count());
    }

    public function test_index_displays_the_live_id(): void
    {
        $user = User::factory()->create();

        LiveStreaming::create([
            'live_id' => 'visible-live-id-123',
            'platform' => 'Showroom',
            'live_date' => '2026-07-10',
        ]);

        $this->actingAs($user)
            ->get(route('live-streaming.index'))
            ->assertOk()
            ->assertSee('visible-live-id-123');
    }
}
