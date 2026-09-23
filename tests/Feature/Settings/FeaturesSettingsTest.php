<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Support\SettingBag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class FeaturesSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_features_activation_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('features.edit'))->assertOk()->assertSee('Features Activation')->assertSee('Sheet Integration');
    }

    public function test_features_page_lists_feature_toggles_without_landing_cards(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('features.edit'))
            ->assertOk()
            ->assertSee('News')
            ->assertSee('Photobooth')
            ->assertDontSee('Galeri di Landing Page')
            ->assertDontSee('Konten yang ditampilkan di Landing Page');
    }

    public function test_features_are_enabled_by_default(): void
    {
        $this->assertTrue(SettingBag::featureEnabled('news'));
        $this->assertTrue(SettingBag::featureEnabled('blog'));
        $this->assertTrue(SettingBag::featureEnabled('magazines'));
    }

    public function test_features_can_be_disabled(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::features.index')
            ->set('newsEnabled', false)
            ->set('blogEnabled', false)
            ->set('magazineEnabled', false)
            ->call('save')
            ->assertHasNoErrors();

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertSame('false', $settings['news_enabled']);
        $this->assertSame('false', $settings['blog_enabled']);
        $this->assertSame('false', $settings['magazines_enabled']);
        $this->assertFalse(SettingBag::featureEnabled('news'));
        $this->assertFalse(SettingBag::featureEnabled('blog'));
        $this->assertFalse(SettingBag::featureEnabled('magazines'));
    }

    public function test_features_can_be_reenabled(): void
    {
        DB::table('app_settings')->insert([
            ['key' => 'news_enabled', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'magazines_enabled', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::features.index')
            ->set('newsEnabled', true)
            ->set('magazineEnabled', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(SettingBag::featureEnabled('news'));
        $this->assertTrue(SettingBag::featureEnabled('magazines'));
    }

    public function test_sheet_integration_is_disabled_by_default_and_can_be_toggled(): void
    {
        $this->assertFalse(SettingBag::sheetIntegrationEnabled());

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::features.index')
            ->set('sheetIntegrationEnabled', true)
            ->call('save')
            ->assertHasNoErrors();

        $settings = DB::table('app_settings')->pluck('value', 'key');

        $this->assertSame('true', $settings['sheet_integration_enabled']);
        $this->assertTrue(SettingBag::sheetIntegrationEnabled());

        Livewire::test('pages::features.index')
            ->set('sheetIntegrationEnabled', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse(SettingBag::sheetIntegrationEnabled());
    }
}
