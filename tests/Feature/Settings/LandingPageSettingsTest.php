<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class LandingPageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_displays_all_sections(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('landing-page.edit'))
            ->assertOk()
            ->assertSee('Landing Page')
            ->assertSee('Judul Hero')
            ->assertSee('Nama 1')
            ->assertSee('Nama 2')
            ->assertSee('Galeri di Landing Page')
            ->assertSee('Konten yang ditampilkan di Landing Page')
            ->assertSee('Hero Image')
            ->assertSee('Upload Gambar')
            ->assertSee('Tampilan Hero Image')
            ->assertSee('Adjustable Height')
            ->assertSee('Contain')
            ->assertSee('Original')
            ->assertSee('Hero Buttons')
            ->assertSee('Youtube Playlist');
    }

    public function test_hero_image_display_mode_is_saved_and_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        $modes = [
            'fit' => ['object-cover', false],
            'contain' => ['object-contain', false],
            'original' => ['object-none', false],
            'adjustable' => ['object-contain', false],
        ];

        foreach ($modes as $mode => [$expected, $escaped]) {
            Livewire::test('pages::landing-page.index')
                ->set('heroImagePath', 'app/hero/fansite.jpg')
                ->set('heroImageDisplay', $mode)
                ->call('save')
                ->assertHasNoErrors();

            $this->assertSame($mode, DB::table('app_settings')->where('key', 'hero_image_display')->value('value'));

            $this->get(route('home'))
                ->assertOk()
                ->assertSee($expected, $escaped);
        }
    }

    public function test_invalid_hero_image_display_mode_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::landing-page.index')
            ->set('heroImageDisplay', 'stretch')
            ->call('save')
            ->assertHasErrors('heroImageDisplay');
    }

    public function test_landing_page_settings_can_be_saved(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::landing-page.index')
            ->set('titleText', 'Hai Fans JKT48')
            ->set('titleColor', '#123456')
            ->set('nameAnimate', true)
            ->set('name1Text', 'Cornelia Vanisa')
            ->set('name1Color', '#112233')
            ->set('name2Text', 'Oniel JKT48')
            ->set('name2Color', '#445566')
            ->set('galleryMode', 'both')
            ->set('welcomeFeedSource', 'blog')
            ->set('heroButton1Enabled', true)
            ->set('heroButton1Label', 'Tonton Live')
            ->set('heroButton1LinkType', 'url')
            ->set('heroButton1LinkValue', 'https://youtube.com/live')
            ->set('heroButton2Enabled', true)
            ->set('heroButton2Label', 'Jadwal')
            ->set('heroButton2LinkType', 'list')
            ->set('heroButton2LinkValue', 'schedule.index')
            ->set('youtubeEmbedEnabled', true)
            ->set('youtubePlaylistUrl', 'https://www.youtube.com/playlist?list=PL123')
            ->set('youtubeDisplayMode', 'cards')
            ->call('save')
            ->assertHasNoErrors();

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertSame('Hai Fans JKT48', $settings['welcome_title_text']);
        $this->assertSame('#123456', $settings['welcome_title_color']);
        $this->assertSame('true', $settings['welcome_name_animate']);
        $this->assertSame('Cornelia Vanisa', $settings['welcome_name_1_text']);
        $this->assertSame('#112233', $settings['welcome_name_1_color']);
        $this->assertSame('Oniel JKT48', $settings['welcome_name_2_text']);
        $this->assertSame('#445566', $settings['welcome_name_2_color']);
        $this->assertSame('both', $settings['gallery_mode']);
        $this->assertSame('blog', $settings['welcome_feed_source']);
        $this->assertSame('true', $settings['hero_button_1_enabled']);
        $this->assertSame('Tonton Live', $settings['hero_button_1_label']);
        $this->assertSame('https://youtube.com/live', $settings['hero_button_1_link_value']);
        $this->assertSame('Jadwal', $settings['hero_button_2_label']);
        $this->assertSame('https://www.youtube.com/playlist?list=PL123', $settings['youtube_playlist_url']);
        $this->assertSame('cards', $settings['youtube_display_mode']);
    }

    public function test_hero_image_can_be_uploaded(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::landing-page.index')
            ->set('heroImageUpload', UploadedFile::fake()->image('hero.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertNotNull($settings['hero_image']);
        Storage::disk('public')->assertExists($settings['hero_image']);
    }

    public function test_youtube_preview_modes_are_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::landing-page.index')
            ->set('youtubeEmbedEnabled', true)
            ->set('youtubePlaylistUrl', 'https://www.youtube.com/playlist?list=PL123')
            ->set('youtubeDisplayMode', 'embed')
            ->assertSee('https://www.youtube.com/embed/videoseries?list=PL123', false);

        Livewire::test('pages::landing-page.index')
            ->set('youtubeEmbedEnabled', true)
            ->set('youtubeDisplayMode', 'cards')
            ->assertSee('Preview Tampilan')
            ->assertSee('Kartu menampilkan 3 video terbaru');
    }

    public function test_custom_title_and_animated_names_render_on_home(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Cornelia Vanisa', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'idol_shortname', 'value' => 'Oniel', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'idol_profile_version', 'value' => 'jkt48', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::landing-page.index')
            ->set('titleText', 'Hai Fans JKT48')
            ->set('titleColor', '#123456')
            ->set('name1Text', 'Cornelia Vanisa')
            ->set('name1Color', '#112233')
            ->set('name2Text', 'Oniel JKT48')
            ->set('name2Color', '#445566')
            ->call('save')
            ->assertHasNoErrors();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hai Fans JKT48')
            ->assertSee('color: #123456', false)
            ->assertSee("data-hero-swap='", false)
            ->assertSee('Cornelia Vanisa')
            ->assertSee('Oniel JKT48')
            ->assertSee('color: #112233', false)
            ->assertSee('#445566', false);
    }

    public function test_invalid_title_color_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::landing-page.index')
            ->set('titleColor', 'neon')
            ->call('save')
            ->assertHasErrors('titleColor');
    }

    public function test_non_super_admin_cannot_access_landing_page(): void
    {
        $this->actingAs(User::factory()->viewOnly()->create());

        $this->get(route('landing-page.edit'))->assertForbidden();
    }
}
