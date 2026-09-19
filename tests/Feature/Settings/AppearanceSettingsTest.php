<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Support\SettingBag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AppearanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_appearance_page_displays_brand_color_and_app_identity_fields(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('appearance.edit'))
            ->assertOk()
            ->assertSee('Brand Color')
            ->assertSee('type="color"', false)
            ->assertSee('App Name / Sidebar Name')
            ->assertSee('Desc App')
            ->assertSee('App Logo')
            ->assertSee('Hero Image')
            ->assertSee('Login Image')
            ->assertSee('Hero Buttons')
            ->assertSee('Tampilkan Youtube Playlist')
            ->assertDontSee('Theme mode');
    }

    public function test_appearance_can_be_saved_and_stale_cache_is_invalidated(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Cache::put('app_settings', ['hero_image' => 'app/hero/old.jpg'], now()->addHour());

        Livewire::test('pages::settings.appearance')
            ->set('brandColor', '#4e5fd4')
            ->set('appName', 'Onielity')
            ->set('descApp', 'Fansite baru')
            ->set('appLogoUpload', UploadedFile::fake()->image('logo.png'))
            ->set('heroImageUpload', UploadedFile::fake()->image('hero.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertSame('#4E5FD4', $settings['brand_color']);
        $this->assertSame('Onielity', $settings['app_name']);
        $this->assertSame('Onielity', $settings['sidebar_name']);
        $this->assertSame('Fansite baru', $settings['desc_app']);
        $this->assertNotNull($settings['app_logo']);
        $this->assertNotNull($settings['hero_image']);
        Storage::disk('public')->assertExists($settings['app_logo']);
        Storage::disk('public')->assertExists($settings['hero_image']);

        $this->assertSame($settings['hero_image'], SettingBag::app()['hero_image']);
    }

    public function test_login_image_can_be_uploaded(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::settings.appearance')
            ->set('appName', 'Onielity')
            ->set('loginImageUpload', UploadedFile::fake()->image('login.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertNotNull($settings['login_image']);
        Storage::disk('public')->assertExists($settings['login_image']);
        $this->assertSame($settings['login_image'], SettingBag::app()['login_image']);
    }

    public function test_hero_buttons_and_youtube_settings_can_be_saved(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::settings.appearance')
            ->set('appName', 'Onielity')
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

        $this->assertSame('true', $settings['hero_button_1_enabled']);
        $this->assertSame('Tonton Live', $settings['hero_button_1_label']);
        $this->assertSame('url', $settings['hero_button_1_link_type']);
        $this->assertSame('https://youtube.com/live', $settings['hero_button_1_link_value']);
        $this->assertSame('Jadwal', $settings['hero_button_2_label']);
        $this->assertSame('list', $settings['hero_button_2_link_type']);
        $this->assertSame('schedule.index', $settings['hero_button_2_link_value']);
        $this->assertSame('true', $settings['youtube_embed_enabled']);
        $this->assertSame('https://www.youtube.com/playlist?list=PL123', $settings['youtube_playlist_url']);
        $this->assertSame('cards', $settings['youtube_display_mode']);
    }

    public function test_brand_color_must_be_a_valid_hex(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::settings.appearance')
            ->set('brandColor', 'not-a-color')
            ->set('appName', 'Onielity')
            ->call('save')
            ->assertHasErrors('brandColor');
    }

    public function test_brand_color_is_applied_to_public_pages(): void
    {
        DB::table('app_settings')->upsert([
            ['key' => 'brand_color', 'value' => '#123456', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        Cache::forget('app_settings');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('--brand-primary: #123456', false);
    }

    public function test_settings_menu_is_rendered_as_horizontal_tabs(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('appearance.edit'))
            ->assertOk()
            ->assertSee('aria-label="Settings"', false)
            ->assertSee('Appearance')
            ->assertSee('Features Activation')
            ->assertSee('Header Menu')
            ->assertDontSee('App Settings');
    }

    public function test_super_admin_only_tabs_are_hidden_for_other_roles(): void
    {
        $this->actingAs(User::factory()->contentCreator()->create());

        $this->get(route('appearance.edit'))
            ->assertOk()
            ->assertSee('Appearance')
            ->assertDontSee('Features Activation')
            ->assertDontSee('Header Menu');
    }
}
