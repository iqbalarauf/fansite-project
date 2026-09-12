<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchedulePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_page_is_displayed_with_calendar_and_list_views(): void
    {
        $this->get(route('schedule.index'))
            ->assertOk()
            ->assertSee('Schedule')
            ->assertSee('data-schedule-view="calendar"', false)
            ->assertSee('data-schedule-view="list"', false)
            ->assertSee('id="schedule-calendar"', false)
            ->assertSee('id="schedule-list"', false);
    }

    public function test_schedule_displays_all_event_types_for_the_selected_month(): void
    {
        $this->seedMonth();

        $this->get(route('schedule.index', ['month' => 9, 'year' => 2026]))
            ->assertOk()
            ->assertSee('September 2026')
            ->assertSee('Pajama Drive')
            ->assertSee('Konser Akbar')
            ->assertSee('Meet & Greet Jakarta')
            ->assertSee('Showroom');
    }

    public function test_schedule_only_shows_events_of_the_selected_month(): void
    {
        $this->seedMonth();

        $this->get(route('schedule.index', ['month' => 8, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Agustus 2026')
            ->assertDontSee('Pajama Drive')
            ->assertDontSee('Konser Akbar')
            ->assertDontSee('Showroom');
    }

    public function test_schedule_normalizes_slash_formatted_show_dates(): void
    {
        DB::table('show_teater')->insert(['show_id' => 1, 'show_date' => '2026/09/10', 'setlist' => 'Slash Setlist']);

        $this->get(route('schedule.index', ['month' => 9, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Slash Setlist');
    }

    public function test_schedule_excludes_soft_deleted_events(): void
    {
        DB::table('concert_events')->insert([
            'event_name' => 'Konser Dihapus',
            'event_date' => '2026-09-15',
            'location' => 'Jakarta',
            'status' => 'on-air',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => now(),
        ]);

        $this->get(route('schedule.index', ['month' => 9, 'year' => 2026]))
            ->assertOk()
            ->assertDontSee('Konser Dihapus');
    }

    public function test_schedule_month_navigation_links_are_present(): void
    {
        $this->get(route('schedule.index', ['month' => 9, 'year' => 2026]))
            ->assertOk()
            ->assertSee('month=8', false)
            ->assertSee('month=10', false);
    }

    public function test_schedule_highlights_the_idol_birthday(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'idol_birth_date', 'value' => '2004-09-13', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        Cache::forget('about_settings');

        $this->get(route('schedule.index', ['month' => 9, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Ulang Tahun Freya');

        $this->get(route('schedule.index', ['month' => 8, 'year' => 2026]))
            ->assertOk()
            ->assertDontSee('Ulang Tahun Freya');
    }

    private function seedMonth(): void
    {
        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => '2026/09/10',
            'setlist' => 'Pajama Drive',
        ]);

        DB::table('concert_events')->insert([
            'event_name' => 'Konser Akbar',
            'event_date' => '2026-09-15',
            'location' => 'Jakarta',
            'status' => 'on-air',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('meet_greet_events')->insert([
            'event_name' => 'Meet & Greet Jakarta',
            'event_date' => '2026-09-20',
            'event_type' => 'meet-greet',
            'location' => 'Jakarta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('live_streaming')->insert([
            'platform' => 'Showroom',
            'live_date' => '2026-09-25',
            'duration' => 90,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
