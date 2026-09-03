<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_access_dashboard_master_data_and_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('show-teater.index'))->assertOk();
        $this->actingAs($user)->get(route('meet-greet-events.index'))->assertOk();
        $this->actingAs($user)->get(route('concert-events.index'))->assertOk();
        $this->actingAs($user)->get(route('live-streaming.index'))->assertOk();
        $this->actingAs($user)->get(route('pages.index'))->assertOk();
    }

    public function test_bank_data_admin_can_access_dashboard_and_master_data_but_not_pages(): void
    {
        $user = User::factory()->bankDataAdmin()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('show-teater.index'))->assertOk();
        $this->actingAs($user)->get(route('meet-greet-events.index'))->assertOk();
        $this->actingAs($user)->get(route('concert-events.index'))->assertOk();
        $this->actingAs($user)->get(route('live-streaming.index'))->assertOk();

        $this->actingAs($user)->get(route('pages.index'))->assertForbidden();
    }

    public function test_content_creator_can_only_access_pages(): void
    {
        $user = User::factory()->contentCreator()->create();

        $this->actingAs($user)->get(route('pages.index'))->assertOk();

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('show-teater.index'))->assertForbidden();
        $this->actingAs($user)->get(route('meet-greet-events.index'))->assertForbidden();
        $this->actingAs($user)->get(route('concert-events.index'))->assertForbidden();
        $this->actingAs($user)->get(route('live-streaming.index'))->assertForbidden();
    }

    public function test_view_only_can_access_every_page_but_cannot_submit_mutating_requests(): void
    {
        $user = User::factory()->viewOnly()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('show-teater.index'))->assertOk();
        $this->actingAs($user)->get(route('meet-greet-events.index'))->assertOk();
        $this->actingAs($user)->get(route('concert-events.index'))->assertOk();
        $this->actingAs($user)->get(route('live-streaming.index'))->assertOk();
        $this->actingAs($user)->get(route('pages.index'))->assertOk();

        $this->actingAs($user)->post(route('show-teater.store'), [
            'show_id' => 999998,
            'show_date' => '2026-06-22',
            'setlist' => 'Pajama Drive',
            'unit_song' => 'Tenshi no Shippo',
        ])->assertForbidden();

        $this->actingAs($user)->post(route('meet-greet-events.store'), [
            'event_type' => 'meet-greet',
            'event_name' => 'Test Event',
            'event_date' => '2026-06-22',
            'location' => 'Jakarta',
        ])->assertForbidden();
    }
}
