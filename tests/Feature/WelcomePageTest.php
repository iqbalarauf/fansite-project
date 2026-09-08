<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_dynamic_idol_and_settings_data(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'idol_description', 'value' => 'Deskripsi freya', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'idol_photo', 'value' => 'idol/freya.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'idol_show_on_welcome', 'value' => 'true', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/freya', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'twitter_url', 'value' => 'https://x.com/freya', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'tiktok_url', 'value' => 'https://tiktok.com/@freya', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        DB::table('app_settings')->upsert([
            ['key' => 'app_name', 'value' => 'Fansite', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero_image', 'value' => 'hero/fansite.jpg', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => now()->subDays(2)->format('Y-m-d'), 'setlist' => 'Setlist A', 'unit_song' => 'Unit A', 'is_global_center' => 1, 'is_us_center' => 0, 'is_the_show_has_event' => null, 'additional_information' => null, 'is_scraped_data' => 1, 'is_member_show' => 1, 'last_fetch_at' => now()],
            ['show_id' => 2, 'show_date' => now()->addDays(5)->format('Y-m-d'), 'setlist' => 'Setlist B', 'unit_song' => 'Unit B', 'is_global_center' => 0, 'is_us_center' => 1, 'is_the_show_has_event' => null, 'additional_information' => null, 'is_scraped_data' => 1, 'is_member_show' => 1, 'last_fetch_at' => now()],
        ]);

        DB::table('live_streaming')->insert([
            ['platform' => 'IDN App', 'live_date' => now()->subDays(1), 'duration' => 120, 'additional_info' => null, 'created_at' => now(), 'updated_at' => now()],
            ['platform' => 'Showroom', 'live_date' => now()->subDays(2), 'duration' => 90, 'additional_info' => null, 'created_at' => now(), 'updated_at' => now()],
            ['platform' => 'Showroom', 'live_date' => now()->subDays(3), 'duration' => 80, 'additional_info' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Selamat Datang di Fansite Freya')
            ->assertSee('Tentang Freya')
            ->assertSee('Deskripsi freya')
            ->assertSee('https://instagram.com/freya')
            ->assertSee('https://x.com/freya')
            ->assertSee('https://tiktok.com/@freya')
            ->assertSee('Data Oniel');
    }

    public function test_homepage_displays_upcoming_events_section_matching_dashboard(): void
    {
        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => now()->addDays(5)->format('Y-m-d'),
            'setlist' => 'Setlist B',
            'unit_song' => 'Unit B',
            'is_global_center' => 0,
            'is_us_center' => 1,
        ]);

        DB::table('concert_events')->insert([
            'event_name' => 'Konser Akbar',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'location' => 'Jakarta',
        ]);

        DB::table('meet_greet_events')->insert([
            'event_name' => 'Meet & Greet Jakarta',
            'event_date' => now()->addDays(20)->format('Y-m-d'),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Event Mendatang')
            ->assertSee('Show Teater')
            ->assertSee('Setlist B')
            ->assertSee('Konser Akbar')
            ->assertSee('Meet & Greet Jakarta')
            ->assertDontSee('Upcoming Show');
    }

    public function test_homepage_uses_performed_show_count_even_when_future_shows_exist(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => now()->subDay()->format('Y-m-d'), 'setlist' => 'Setlist A'],
            ['show_id' => 2, 'show_date' => now()->addDays(3)->format('Y-m-d'), 'setlist' => 'Setlist B'],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Upcoming Show');
    }
}
