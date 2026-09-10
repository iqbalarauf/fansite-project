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

class AppSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_settings_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('app-settings.edit'))->assertOk();
    }

    public function test_app_settings_can_be_updated_and_stale_cache_is_invalidated(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Cache::put('app_settings', ['hero_image' => 'app/hero/old.jpg'], now()->addHour());

        Livewire::test('pages::settings.app-settings')
            ->set('appName', 'Onielity')
            ->set('sidebarName', 'Onielity')
            ->set('descApp', 'Fansite baru')
            ->set('heroImageUpload', UploadedFile::fake()->image('hero.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertSame('Onielity', $settings['app_name']);
        $this->assertSame('Onielity', $settings['sidebar_name']);
        $this->assertSame('Fansite baru', $settings['desc_app']);
        $this->assertNotNull($settings['hero_image']);
        Storage::disk('public')->assertExists($settings['hero_image']);

        $this->assertSame($settings['hero_image'], SettingBag::app()['hero_image']);
        $this->assertNotSame('app/hero/old.jpg', SettingBag::app()['hero_image']);
    }
}
