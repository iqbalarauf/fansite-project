<?php

namespace Tests\Feature;

use App\Models\ConcertEvents;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConcertEventsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_open_concert_events_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('concert-events.index'));

        $response->assertOk();
        $response->assertSee('Concert & Events', false);
    }

    public function test_user_can_create_concert_event(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('concert-events.store'), [
            'event_name' => 'Summer Dome Concert',
            'event_date' => '2026-07-20',
            'location' => 'Tokyo Dome',
            'status' => 'on-air',
            'purchase_link' => 'https://example.com/concert-ticket',
        ]);

        $response->assertRedirect(route('concert-events.index'));

        $this->assertDatabaseHas('concert_events', [
            'event_name' => 'Summer Dome Concert',
            'location' => 'Tokyo Dome',
            'status' => 'on-air',
        ]);
    }

    public function test_status_is_required_for_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('concert-events.store'), [
            'event_name' => 'No Status Event',
            'event_date' => '2026-08-10',
            'location' => 'Osaka Jo Hall',
            'purchase_link' => null,
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_user_can_soft_delete_concert_event(): void
    {
        $user = User::factory()->create();

        $event = ConcertEvents::create([
            'event_name' => 'Delete Concert Event',
            'event_date' => '2026-09-01',
            'location' => 'Yokohama Arena',
            'status' => 'off-air',
        ]);

        $response = $this->actingAs($user)->delete(route('concert-events.destroy', $event));

        $response->assertRedirect(route('concert-events.index'));
        $this->assertSoftDeleted('concert_events', ['id' => $event->id]);
    }
}
