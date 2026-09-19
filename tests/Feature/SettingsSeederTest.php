<?php

namespace Tests\Feature;

use Database\Seeders\AboutSeeder;
use Database\Seeders\AppSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_settings_seeder_populates_current_keys_and_empties_media(): void
    {
        $this->seed(AppSettingsSeeder::class);

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertSame('#6C7CE8', $settings['brand_color']);
        $this->assertSame('#A5B4FC', $settings['brand_color_secondary']);
        $this->assertSame('#FFD166', $settings['brand_color_tertiary']);
        $this->assertSame('cards', $settings['youtube_display_mode']);
        $this->assertSame('news', $settings['welcome_feed_source']);
        $this->assertSame('default', $settings['header_menu_mode']);
        $this->assertSame('true', $settings['hero_button_1_enabled']);
        $this->assertSame('#schedule', $settings['hero_button_2_link_value']);

        $this->assertNull($settings['app_logo']);
        $this->assertNull($settings['hero_image']);
        $this->assertNull($settings['login_image']);
        $this->assertNull($settings['youtube_playlist_url']);
    }

    public function test_about_seeder_populates_current_keys_and_empties_media_and_arrays(): void
    {
        $this->seed(AboutSeeder::class);

        $settings = DB::table('about_settings')->pluck('value', 'key');

        $this->assertSame('your-idol-name', $settings['idol_slug']);
        $this->assertSame('jkt48', $settings['idol_profile_version']);
        $this->assertSame('true', $settings['kabesha_enabled']);
        $this->assertSame('default', $settings['fanbase_history_source']);
        $this->assertSame('true', $settings['fanbase_structure_enabled']);
        $this->assertSame('true', $settings['fanbase_activities_enabled']);

        $this->assertNull($settings['idol_photo']);
        $this->assertNull($settings['kabesha_items']);
        $this->assertNull($settings['kabesha_photos']);
        $this->assertNull($settings['fanbase_logo']);
        $this->assertNull($settings['fanbase_gallery']);
        $this->assertNull($settings['fanbase_gallery_items']);
        $this->assertNull($settings['fanbase_history_items']);
        $this->assertNull($settings['fanbase_cta_background']);
    }
}
