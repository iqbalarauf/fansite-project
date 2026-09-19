<?php

namespace Tests\Feature;

use App\Support\IdolTheaterStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_idol_page_displays_idol_information(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya'],
            ['key' => 'idol_description', 'value' => 'Deskripsi Freya JKT48'],
            ['key' => 'idol_achievements', 'value' => "Pemenang Gaya Terfavorit\nJuara 1 Senbatsu Sousenkyo"],
            ['key' => 'idol_discography', 'value' => "Jacket Doki Doki Syndrome\nShekina"],
            ['key' => 'idol_jikoshoukai', 'value' => 'Perkenalan singkat dari Freya'],
            ['key' => 'idol_birth_date', 'value' => '2004-02-13'],
            ['key' => 'idol_birth_place', 'value' => 'Tangerang'],
            ['key' => 'idol_blood_type', 'value' => 'O'],
            ['key' => 'idol_horoscope', 'value' => 'Aquarius'],
            ['key' => 'idol_social_media_instagram', 'value' => 'https://instagram.com/freya'],
            ['key' => 'idol_social_media_twitter', 'value' => 'https://x.com/freya'],
            ['key' => 'idol_social_media_tiktok', 'value' => 'https://tiktok.com/@freya'],
        ], ['key'], ['value', 'updated_at']);

        $expectedBirthDate = Carbon::parse('2004-02-13')->locale('id')->isoFormat('D MMMM YYYY');

        $this->get(route('about.show'))
            ->assertOk()
            ->assertSee('About Oshimen', false)
            ->assertSee('Freya')
            ->assertSee('Deskripsi Freya JKT48')
            ->assertSee('Pemenang Gaya Terfavorit')
            ->assertSee('Juara 1 Senbatsu Sousenkyo')
            ->assertSee('Jacket Doki Doki Syndrome')
            ->assertSee('Perkenalan singkat dari Freya')
            ->assertSee($expectedBirthDate, false)
            ->assertSee('Tangerang')
            ->assertSee('O')
            ->assertSee('Aquarius')
            ->assertSee('https://instagram.com/freya');
    }

    public function test_about_fansite_page_displays_fansite_information(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'fanbase_name', 'value' => 'Wota Nusantara'],
            ['key' => 'fanbase_description', 'value' => 'Komunitas fanbase terbesar di Indonesia'],
            ['key' => 'fanbase_activities', 'value' => "Nobar Konser\nFangirling Bareng"],
            ['key' => 'fanbase_gallery', 'value' => json_encode(['about/fansite/gallery/g1.jpg', 'about/fansite/gallery/g2.jpg'])],
            ['key' => 'fanbase_cta_enabled', 'value' => 'true'],
            ['key' => 'fanbase_cta_title', 'value' => 'Gabung Menjadi Bagian dari Keluarga'],
            ['key' => 'fanbase_cta_button1_text', 'value' => 'Join Discord'],
            ['key' => 'fanbase_cta_button1_link', 'value' => 'https://discord.gg/example'],
            ['key' => 'fanbase_cta_button2_text', 'value' => 'Follow X'],
            ['key' => 'fanbase_cta_button2_link', 'value' => 'https://x.com/example'],
            ['key' => 'idol_name', 'value' => 'Freya'],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'wota-nusantara'))
            ->assertOk()
            ->assertSee('About Fansite', false)
            ->assertSee('Wota Nusantara')
            ->assertSee('Komunitas fanbase terbesar di Indonesia')
            ->assertSee('Nobar Konser')
            ->assertSee('Fangirling Bareng')
            ->assertSee('/storage/about/fansite/gallery/g1.jpg', false)
            ->assertSee('/storage/about/fansite/gallery/g2.jpg', false)
            ->assertSee('Gabung Menjadi Bagian dari Keluarga')
            ->assertSee('Join Discord')
            ->assertSee('Follow X')
            ->assertDontSee('Kembali ke Beranda');
    }

    public function test_about_fansite_page_shows_structure_as_list_and_gallery_captions(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'fanbase_name', 'value' => 'Wota Nusantara'],
            ['key' => 'fanbase_structure', 'value' => "Ketua: Oniel\nWakil: Freya"],
            ['key' => 'fanbase_gallery_items', 'value' => json_encode([
                ['photo' => 'about/fansite/gallery/1.jpg', 'caption' => 'Foto bersama'],
                ['photo' => 'about/fansite/gallery/2.jpg', 'caption' => 'Nobar'],
            ])],
            ['key' => 'idol_name', 'value' => 'Freya'],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'wota-nusantara'))
            ->assertOk()
            ->assertSee('Struktur Organisasi')
            ->assertSee('Ketua: Oniel')
            ->assertSee('Wakil: Freya')
            ->assertSee('/storage/about/fansite/gallery/1.jpg', false)
            ->assertSee('Foto bersama')
            ->assertSee('Nobar')
            ->assertSee('data-fanbase-carousel', false)
            ->assertSee('data-fanbase-item', false)
            ->assertSee('<figcaption', false);
    }

    public function test_about_fansite_page_links_sejarah_kami_to_custom_page_when_selected(): void
    {
        $pageId = DB::table('custom_pages')->insertGetId([
            'title' => 'Sejarah Fansite',
            'slug' => 'sejarah-fansite',
            'status' => 'published',
            'blocks' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('about_settings')->upsert([
            ['key' => 'fanbase_name', 'value' => 'Wota Nusantara'],
            ['key' => 'fanbase_activities', 'value' => 'Nobar'],
            ['key' => 'fanbase_history_enabled', 'value' => 'true'],
            ['key' => 'fanbase_history_source', 'value' => 'custom'],
            ['key' => 'fanbase_history_custom_page_id', 'value' => (string) $pageId],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'wota-nusantara'))
            ->assertOk()
            ->assertSee('Sejarah Kami')
            ->assertSee('href="'.route('custom-pages.show', 'sejarah-fansite').'"', false)
            ->assertDontSee('data-history-open', false);
    }

    public function test_about_fansite_page_shows_sejarah_kami_modal_for_default_pages(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'fanbase_name', 'value' => 'Wota Nusantara'],
            ['key' => 'fanbase_activities', 'value' => 'Nobar'],
            ['key' => 'fanbase_history_enabled', 'value' => 'true'],
            ['key' => 'fanbase_history_source', 'value' => 'default'],
            ['key' => 'fanbase_history_items', 'value' => json_encode([
                ['photo' => 'about/fansite/history/1.jpg', 'description' => 'Awal berdirinya fanbase'],
            ])],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'wota-nusantara'))
            ->assertOk()
            ->assertSee('Sejarah Kami')
            ->assertSee('data-history-open', false)
            ->assertSee('data-history-close', false)
            ->assertSee('/storage/about/fansite/history/1.jpg', false)
            ->assertSee('Awal berdirinya fanbase');
    }

    public function test_about_fansite_page_hides_structure_and_activities_when_disabled(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'fanbase_name', 'value' => 'Wota Nusantara'],
            ['key' => 'fanbase_structure', 'value' => 'Ketua: Oniel'],
            ['key' => 'fanbase_structure_enabled', 'value' => 'false'],
            ['key' => 'fanbase_activities', 'value' => 'Nobar Konser'],
            ['key' => 'fanbase_activities_enabled', 'value' => 'false'],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'wota-nusantara'))
            ->assertOk()
            ->assertDontSee('Struktur Organisasi')
            ->assertDontSee('Kegiatan Fanbase')
            ->assertDontSee('Ketua: Oniel')
            ->assertDontSee('Nobar Konser');
    }

    public function test_about_fansite_page_shows_structure_and_activities_by_default(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'fanbase_name', 'value' => 'Wota Nusantara'],
            ['key' => 'fanbase_structure', 'value' => 'Ketua: Oniel'],
            ['key' => 'fanbase_activities', 'value' => 'Nobar Konser'],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'wota-nusantara'))
            ->assertOk()
            ->assertSee('Struktur Organisasi')
            ->assertSee('Ketua: Oniel')
            ->assertSee('Kegiatan Fanbase')
            ->assertSee('Nobar Konser');
    }

    public function test_public_header_about_dropdown_lists_idol_and_fanbase_names(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya'],
            ['key' => 'fanbase_name', 'value' => 'Wota Nusantara'],
        ], ['key'], ['value', 'updated_at']);

        $idolHref = route('about.show', 'freya');
        $fansiteHref = route('about.show', 'wota-nusantara');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('About')
            ->assertSee('Freya')
            ->assertSee('Wota Nusantara')
            ->assertSee('href="'.$idolHref.'"', false)
            ->assertSee('href="'.$fansiteHref.'"', false);

        $this->get(route('about.show', 'freya'))
            ->assertOk()
            ->assertSee('href="'.$idolHref.'"', false)
            ->assertSee('href="'.$fansiteHref.'"', false);
    }

    public function test_about_idol_page_displays_theater_statistics(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya'],
            ['key' => 'idol_slug', 'value' => 'freya'],
        ], ['key'], ['value', 'updated_at']);

        DB::table('show_teater_categories')->insert([
            'name' => 'Pajama Drive',
            'jp_name' => 'パジャマドライブ',
            'type' => 'setlist',
            'is_active' => 1,
        ]);

        DB::table('show_teater_categories')->insert([
            'name' => 'Tenshi no Shippo',
            'jp_name' => '天使のしっぽ',
            'type' => 'unit_song',
        ]);

        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026/09/10', 'setlist' => 'Pajama Drive', 'unit_song' => 'Tenshi no Shippo', 'is_member_show' => 1, 'is_global_center' => 1, 'is_us_center' => 1],
            ['show_id' => 2, 'show_date' => '2025/01/01', 'setlist' => 'Pajama Drive', 'unit_song' => 'Tenshi no Shippo, Higurashi no Koi', 'is_member_show' => 1, 'is_global_center' => 0, 'is_us_center' => 0],
        ]);

        $this->get(route('about.show', 'freya'))
            ->assertOk()
            ->assertSee('Show Teater')
            ->assertSee('Pajama Drive')
            ->assertSee('Setlist Aktif')
            ->assertSee('Unit Song')
            ->assertSee('Tenshi no Shippo')
            ->assertSee('天使のしっぽ')
            ->assertSee('On Going')
            ->assertSee('Global Center')
            ->assertSee('Center Unit Song')
            ->assertSee('Tahun Ini');
    }

    public function test_unit_song_separator_is_semicolon_only(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026/09/10', 'setlist' => 'Set A', 'unit_song' => 'Title, With Comma', 'is_member_show' => 1],
            ['show_id' => 2, 'show_date' => '2026/09/11', 'setlist' => 'Set A', 'unit_song' => 'Song A; Song B', 'is_member_show' => 1],
        ]);

        $names = collect(app(IdolTheaterStats::class)->build()['unit_songs'])->pluck('name')->all();

        $this->assertContains('Title, With Comma', $names);
        $this->assertContains('Song A', $names);
        $this->assertContains('Song B', $names);
        $this->assertCount(3, $names);
    }

    public function test_center_cards_merge_setlists_and_highlight_active_ones(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya'],
            ['key' => 'idol_slug', 'value' => 'freya'],
        ], ['key'], ['value', 'updated_at']);

        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => now()->format('Y/m/d'), 'setlist' => 'Setlist Sekarang', 'is_member_show' => 1, 'is_global_center' => 1, 'is_us_center' => 1],
            ['show_id' => 2, 'show_date' => now()->subYear()->format('Y/m/d'), 'setlist' => 'Setlist Lama', 'is_member_show' => 1, 'is_global_center' => 1, 'is_us_center' => 1],
        ]);

        $this->get(route('about.show', 'freya'))
            ->assertOk()
            ->assertSee('Setlist')
            ->assertDontSee('Setlist (All)')
            ->assertDontSee('Setlist ('.now()->year.')')
            ->assertSee('bg-indigo-50 text-indigo-600', false)
            ->assertSee('bg-slate-100 text-slate-600', false);
    }

    public function test_twitter_card_matches_the_other_social_cards(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya'],
            ['key' => 'idol_social_media_twitter', 'value' => 'https://x.com/freya'],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show'))
            ->assertOk()
            ->assertSee('href="https://x.com/freya"', false)
            ->assertSee('Follow di Twitter')
            ->assertSee('h-80', false);
    }

    public function test_idol_page_shows_kabesha_grid_with_per_photo_details(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya'],
            ['key' => 'idol_slug', 'value' => 'freya'],
            ['key' => 'kabesha_items', 'value' => json_encode([
                ['photo' => 'about/kabesha/1.jpg', 'title' => 'Kabesha Satu', 'duration_from' => '2026-01-01', 'duration_to' => '2026-01-31'],
                ['photo' => 'about/kabesha/2.jpg', 'title' => 'Kabesha Dua', 'duration_from' => null, 'duration_to' => null],
            ])],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'freya'))
            ->assertOk()
            ->assertSee('Kabesha Satu')
            ->assertSee('Kabesha Dua')
            ->assertSee('data-kabesha-carousel', false)
            ->assertSee('data-kabesha-item', false)
            ->assertSee('object-contain', false)
            ->assertSee('Duration: 1 Januari 2026 – 31 Januari 2026', false)
            ->assertSee('data-collapse-key="idol-unit-song"', false)
            ->assertSee('data-collapse-key="idol-centers"', false);
    }

    public function test_on_going_falls_back_to_previous_show_when_latest_is_member_show_is_null(): void
    {
        DB::table('show_teater_categories')->insert([
            ['name' => 'Pajama Drive', 'jp_name' => 'パジャマドライブ', 'type' => 'setlist', 'is_active' => 1],
            ['name' => 'Tenshi no Shippo', 'jp_name' => '天使のしっぽ', 'type' => 'unit_song', 'is_active' => 1],
            ['name' => 'Higurashi no Koi', 'jp_name' => 'ひぐらしの恋', 'type' => 'unit_song', 'is_active' => 1],
        ]);

        DB::table('show_teater')->insert([
            ['show_id' => 3, 'show_date' => '2026/09/10', 'setlist' => 'Pajama Drive', 'unit_song' => 'Higurashi no Koi', 'is_member_show' => null],
            ['show_id' => 2, 'show_date' => '2026/09/05', 'setlist' => 'Pajama Drive', 'unit_song' => 'Tenshi no Shippo', 'is_member_show' => 1],
        ]);

        $songs = collect(app(IdolTheaterStats::class)->build()['unit_songs'])->keyBy('name');

        $this->assertTrue($songs['Tenshi no Shippo']['on_going']);
        $this->assertFalse($songs['Higurashi no Koi']['on_going']);
    }

    public function test_jkt48_idol_version_shows_jikoshoukai_and_blood_type_with_oshimen_term(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya'],
            ['key' => 'idol_slug', 'value' => 'freya'],
            ['key' => 'idol_profile_version', 'value' => 'jkt48'],
            ['key' => 'idol_jikoshoukai', 'value' => 'Perkenalan singkat'],
            ['key' => 'idol_blood_type', 'value' => 'O'],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'freya'))
            ->assertOk()
            ->assertSee('About Oshimen', false)
            ->assertSee('Jikoshoukai')
            ->assertSee('Perkenalan singkat')
            ->assertSee('Golongan Darah');
    }

    public function test_general_idol_version_hides_jkt48_fields_and_uses_idol_bias_term(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya'],
            ['key' => 'idol_slug', 'value' => 'freya'],
            ['key' => 'idol_profile_version', 'value' => 'general'],
            ['key' => 'idol_jikoshoukai', 'value' => 'Perkenalan singkat'],
            ['key' => 'idol_blood_type', 'value' => 'O'],
            ['key' => 'idol_horoscope', 'value' => 'Aquarius'],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'freya'))
            ->assertOk()
            ->assertSee('About Idol/Bias', false)
            ->assertDontSee('Jikoshoukai')
            ->assertDontSee('Perkenalan singkat')
            ->assertDontSee('Golongan Darah')
            ->assertSee('Aquarius');
    }

    public function test_idol_kabesha_section_can_be_disabled(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya'],
            ['key' => 'idol_slug', 'value' => 'freya'],
            ['key' => 'kabesha_enabled', 'value' => 'false'],
            ['key' => 'kabesha_items', 'value' => json_encode([
                ['photo' => 'about/kabesha/1.jpg', 'title' => 'Kabesha Satu', 'duration_from' => null, 'duration_to' => null],
            ])],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'freya'))
            ->assertOk()
            ->assertDontSee('id="kabesha"', false)
            ->assertDontSee('Kabesha Satu');
    }

    public function test_idol_kabesha_default_title_is_applied_to_untitled_items(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya'],
            ['key' => 'idol_slug', 'value' => 'freya'],
            ['key' => 'kabesha_enabled', 'value' => 'true'],
            ['key' => 'kabesha_default_title', 'value' => 'Momen Spesial'],
            ['key' => 'kabesha_items', 'value' => json_encode([
                ['photo' => 'about/kabesha/1.jpg', 'title' => '', 'duration_from' => null, 'duration_to' => null],
            ])],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show', 'freya'))
            ->assertOk()
            ->assertSee('Momen Spesial');
    }
}
