<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShowTeaterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_storing_show_flushes_cache(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Put something in the cache
        Cache::put('about_settings', 'cached_data', 3600);
        $this->assertTrue(Cache::has('about_settings'));

        $nextShowId = (int) DB::table('show_teater')->max('show_id') + 1;

        // Post to store show teater
        $response = $this->post(route('show-teater.store'), [
            'show_id' => $nextShowId,
            'show_date' => '2026-06-22',
            'setlist' => 'Pajama Drive',
            'unit_song' => 'Tenshi no Shippo',
        ]);

        $response->assertRedirect(route('show-teater.index'));

        // Assert cache is flushed
        $this->assertFalse(Cache::has('about_settings'));
    }

    public function test_storing_and_displaying_double_unit_songs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Setup categories
        $setlistId = DB::table('show_teater_categories')->insertGetId([
            'name' => 'Pajama Drive',
            'jp_name' => 'パジャマドライブ',
            'type' => 'setlist',
            'status' => 1,
        ]);

        DB::table('show_teater_categories')->insert([
            [
                'name' => 'Tenshi no Shippo',
                'jp_name' => '天使のしっぽ',
                'type' => 'unit_song',
                'setlist_id' => $setlistId,
                'status' => 1,
            ],
            [
                'name' => 'Higurashi no Koi',
                'jp_name' => 'ひぐらしの恋',
                'type' => 'unit_song',
                'setlist_id' => $setlistId,
                'status' => 1,
            ],
        ]);

        $nextShowId = (int) DB::table('show_teater')->max('show_id') + 1;

        // Post to store a show with double unit songs
        $response = $this->post(route('show-teater.store'), [
            'show_id' => $nextShowId,
            'show_date' => '2026-06-22',
            'setlist' => 'Pajama Drive',
            'double_us' => '1',
            'unit_song' => 'Tenshi no Shippo',
            'unit_song_2' => 'Higurashi no Koi',
        ]);

        $response->assertRedirect(route('show-teater.index'));

        // Check if database contains the formatted double unit song
        $this->assertDatabaseHas('show_teater', [
            'show_id' => $nextShowId,
            'unit_song' => 'Tenshi no Shippo, Higurashi no Koi',
        ]);

        // Get index page and verify correct formatting (Japanese title mapping works for both)
        $response = $this->get(route('show-teater.index'));
        $response->assertStatus(200);
        $response->assertSee('Tenshi no Shippo (天使のしっぽ), Higurashi no Koi (ひぐらしの恋)', false);
    }

    public function test_updating_show_with_double_unit_songs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Setup categories
        $setlistId = DB::table('show_teater_categories')->insertGetId([
            'name' => 'Pajama Drive',
            'jp_name' => 'パジャマドライブ',
            'type' => 'setlist',
            'status' => 1,
        ]);

        DB::table('show_teater_categories')->insert([
            [
                'name' => 'Tenshi no Shippo',
                'jp_name' => '天使のしっぽ',
                'type' => 'unit_song',
                'setlist_id' => $setlistId,
                'status' => 1,
            ],
            [
                'name' => 'Higurashi no Koi',
                'jp_name' => 'ひぐらしの恋',
                'type' => 'unit_song',
                'setlist_id' => $setlistId,
                'status' => 1,
            ],
        ]);

        $nextShowId = (int) DB::table('show_teater')->max('show_id') + 1;

        // Insert initial single US show
        DB::table('show_teater')->insert([
            'show_id' => $nextShowId,
            'show_date' => '2026-06-22',
            'setlist' => 'Pajama Drive',
            'unit_song' => 'Tenshi no Shippo',
            'is_global_center' => 0,
            'is_us_center' => 0,
        ]);

        // Put to update it to double US
        $response = $this->put(route('show-teater.update', $nextShowId), [
            'show_date' => '2026-06-22',
            'setlist' => 'Pajama Drive',
            'double_us' => '1',
            'unit_song' => 'Tenshi no Shippo',
            'unit_song_2' => 'Higurashi no Koi',
        ]);

        $response->assertRedirect(route('show-teater.index'));

        // Check if database contains updated double unit song
        $this->assertDatabaseHas('show_teater', [
            'show_id' => $nextShowId,
            'unit_song' => 'Tenshi no Shippo, Higurashi no Koi',
        ]);
    }
}
