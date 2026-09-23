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
            ->assertSee('Primer')
            ->assertSee('Sekunder')
            ->assertSee('Tersier')
            ->assertSee('App Name / Sidebar Name')
            ->assertSee('Desc App')
            ->assertSee('App Logo')
            ->assertSee('Login Image')
            ->assertDontSee('Theme mode');
    }

    public function test_appearance_can_be_saved_and_stale_cache_is_invalidated(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Cache::put('app_settings', ['hero_image' => 'app/hero/old.jpg'], now()->addHour());

        Livewire::test('pages::appearance.index')
            ->set('brandColor', '#4e5fd4')
            ->set('appName', 'Onielity')
            ->set('descApp', 'Fansite baru')
            ->set('appLogoUpload', UploadedFile::fake()->image('logo.png'))
            ->call('save')
            ->assertHasNoErrors();

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertSame('#4E5FD4', $settings['brand_color']);
        $this->assertSame('Onielity', $settings['app_name']);
        $this->assertSame('Onielity', $settings['sidebar_name']);
        $this->assertSame('Fansite baru', $settings['desc_app']);
        $this->assertNotNull($settings['app_logo']);
        Storage::disk('public')->assertExists($settings['app_logo']);
    }

    public function test_login_image_can_be_uploaded(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::appearance.index')
            ->set('appName', 'Onielity')
            ->set('loginImageUpload', UploadedFile::fake()->image('login.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertNotNull($settings['login_image']);
        Storage::disk('public')->assertExists($settings['login_image']);
        $this->assertSame($settings['login_image'], SettingBag::app()['login_image']);
    }

    public function test_three_brand_colors_can_be_saved_and_applied(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::appearance.index')
            ->set('appName', 'Onielity')
            ->set('brandColor', '#112233')
            ->set('brandColorSecondary', '#445566')
            ->set('brandColorTertiary', '#778899')
            ->call('save')
            ->assertHasNoErrors();

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertSame('#112233', $settings['brand_color']);
        $this->assertSame('#445566', $settings['brand_color_secondary']);
        $this->assertSame('#778899', $settings['brand_color_tertiary']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('--brand-primary: #112233', false)
            ->assertSee('--brand-secondary: #445566', false)
            ->assertSee('--brand-tertiary: #778899', false)
            ->assertSee('--color-indigo-600: #112233', false)
            ->assertSee('--color-violet-600: #445566', false)
            ->assertSee('--color-yellow-500: #778899', false);
    }

    public function test_appearance_layout_uses_multi_column_grids(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('appearance.edit'))
            ->assertOk()
            ->assertSeeHtml('md:grid-cols-3')
            ->assertSeeHtml('md:grid-cols-2');
    }

    public function test_brand_color_must_be_a_valid_hex(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::appearance.index')
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

    public function test_standalone_settings_pages_do_not_use_the_settings_tabs(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('appearance.edit'))
            ->assertOk()
            ->assertSee('Appearance')
            ->assertDontSee('aria-label="Settings"', false);
    }

    public function test_configuration_sidebar_links_to_standalone_settings_pages_for_super_admin(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('landing-page.edit'))
            ->assertSee(route('appearance.edit'))
            ->assertSee(route('features.edit'))
            ->assertSee(route('header-menu.edit'));
    }

    public function test_configuration_sidebar_hides_standalone_settings_pages_for_other_roles(): void
    {
        $this->actingAs(User::factory()->viewOnly()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('features.edit'))
            ->assertDontSee(route('header-menu.edit'));
    }
}
