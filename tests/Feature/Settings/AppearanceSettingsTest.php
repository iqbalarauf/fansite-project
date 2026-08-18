<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppearanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_appearance_page_displays_visual_palette_options(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('appearance.edit'))
            ->assertOk()
            ->assertSee('Visual palette')
            ->assertSee('Periwinkle')
            ->assertSee('Warm Gold');
    }

    public function test_app_settings_page_displays_required_fields(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('app-settings.edit'))
            ->assertOk()
            ->assertSee('App Name')
            ->assertSee('Sidebar Name')
            ->assertSee('Desc App')
            ->assertSee('App Logo')
            ->assertSee('Hero Image')
            ->assertSee('Login Image');
    }
}
