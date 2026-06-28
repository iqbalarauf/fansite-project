<?php

namespace Tests\Feature;

use App\Models\MeetGreetEvents;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MeetGreetEventsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_open_meet_greet_events_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('meet-greet-events.index'));

        $response->assertOk();
        $response->assertSee('Meet &amp; Greet Events', false);
    }

    public function test_video_call_requires_second_event_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('meet-greet-events.store'), [
            'event_name' => 'Video Call Batch 1',
            'event_type' => 'video-call',
            'event_date' => '2026-07-01',
            'event_date_2' => '',
        ]);

        $response->assertSessionHasErrors('event_date_2');
    }

    public function test_user_can_create_event_with_purchase_link(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('meet-greet-events.store'), [
            'event_name' => 'Meet & Greet Summer Fest',
            'event_type' => 'meet-greet',
            'event_date' => '2026-07-10',
            'ticket_sale_datetime' => '2026-07-01',
            'purchase_link' => 'https://example.com/tickets',
        ]);

        $response->assertRedirect(route('meet-greet-events.index'));

        $this->assertDatabaseHas('meet_greet_events', [
            'event_name' => 'Meet & Greet Summer Fest',
            'event_type' => 'meet-greet',
            'purchase_link' => 'https://example.com/tickets',
        ]);
    }

    public function test_user_can_soft_delete_event(): void
    {
        $user = User::factory()->create();

        $event = MeetGreetEvents::create([
            'event_name' => 'Delete Me Event',
            'event_type' => 'meet-greet',
            'event_date' => '2026-08-01',
        ]);

        $response = $this->actingAs($user)->delete(route('meet-greet-events.destroy', $event));

        $response->assertRedirect(route('meet-greet-events.index'));
        $this->assertSoftDeleted('meet_greet_events', ['id' => $event->id]);
    }
}
