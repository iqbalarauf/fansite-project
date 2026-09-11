<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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
            ->assertSee('Selamat Datang di Fansite')
            ->assertSee('Freya')
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
            ->assertSee('Upcoming Show');
    }

    public function test_homepage_uses_performed_show_count_even_when_future_shows_exist(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => now()->subDay()->format('Y-m-d'), 'setlist' => 'Setlist A'],
            ['show_id' => 2, 'show_date' => now()->addDays(3)->format('Y-m-d'), 'setlist' => 'Setlist B'],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Upcoming Show');
    }

    public function test_homepage_links_upcoming_events_with_purchase_link(): void
    {
        DB::table('concert_events')->insert([
            'event_name' => 'Konser Bertiket',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'location' => 'Jakarta',
            'purchase_link' => 'https://tiket.example/konser',
        ]);

        DB::table('meet_greet_events')->insert([
            'event_name' => 'Meet & Greet Bertiket',
            'event_date' => now()->addDays(15)->format('Y-m-d'),
            'purchase_link' => 'https://tiket.example/mg',
        ]);

        DB::table('meet_greet_events')->insert([
            'event_name' => 'Meet & Greet Gratis',
            'event_date' => now()->addDays(20)->format('Y-m-d'),
        ]);

        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => now()->addDays(5)->format('Y-m-d'), 'setlist' => 'Setlist Biasa'],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="https://tiket.example/konser"', false)
            ->assertSee('href="https://tiket.example/mg"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('Konser Bertiket</a>', false)
            ->assertSee('Meet &amp; Greet Bertiket</a>', false)
            ->assertDontSee('Meet & Greet Gratis</a>', false)
            ->assertDontSee('Setlist Biasa</a>', false);
    }

    public function test_homepage_shows_last_event_date_and_upcoming_show_badge(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => now()->subDays(2)->format('Y-m-d'), 'setlist' => 'Setlist Lalu'],
            ['show_id' => 2, 'show_date' => now()->addDays(7)->format('Y-m-d'), 'setlist' => 'Setlist Depan'],
        ]);

        DB::table('concert_events')->insert([
            'event_name' => 'Konser Lalu',
            'event_date' => now()->subDays(1)->format('Y-m-d'),
            'location' => 'Bandung',
        ]);

        $expectedLastEvent = Carbon::parse(now()->subDays(1))->locale('id')->isoFormat('D MMMM YYYY');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Last Event: '.$expectedLastEvent, false)
            ->assertSee('1 Upcoming Show', false)
            ->assertDontSee('Live update', false);
    }

    public function test_homepage_lists_latest_news_when_enabled(): void
    {
        DB::table('news_posts')->insert([
            'title' => 'Berita Terkini',
            'slug' => 'berita-terkini',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('news_posts')->insert([
            'title' => 'Berita Draft',
            'slug' => 'berita-draft',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Berita Terbaru')
            ->assertSee('Berita Terkini')
            ->assertDontSee('Berita Draft')
            ->assertSee(route('news.index'), false);
    }

    public function test_homepage_hides_news_when_disabled(): void
    {
        DB::table('app_settings')->upsert([
            ['key' => 'news_enabled', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        Cache::forget('app_settings');

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Berita Terbaru');
    }
}
