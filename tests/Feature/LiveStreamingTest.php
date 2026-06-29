<?php

namespace Tests\Feature;

use App\Models\LiveStreaming;
use App\Models\User;
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
}
