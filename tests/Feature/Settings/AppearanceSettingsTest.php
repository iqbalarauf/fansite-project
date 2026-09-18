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
