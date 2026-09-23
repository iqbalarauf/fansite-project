<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppBrandingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_welcome_page_uses_app_name_for_title_and_app_logo_as_favicon(): void
    {
        DB::table('app_settings')->upsert([
            ['key' => 'app_name', 'value' => 'FANSITE KUSTOM', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_logo', 'value' => 'app/logo-kustom.png', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('<title>FANSITE KUSTOM</title>', false)
            ->assertSee('storage/app/logo-kustom.png', false)
            ->assertDontSee('/favicon.ico', false);
    }

    public function test_welcome_page_falls_back_to_default_favicon_without_app_logo(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('<link rel="icon" href="/favicon.ico" sizes="any">', false)
            ->assertSee('/favicon.svg', false)
            ->assertDontSee('storage/app/', false);
    }

    public function test_app_layout_uses_app_name_for_title_and_app_logo_as_favicon(): void
    {
        DB::table('app_settings')->upsert([
            ['key' => 'app_name', 'value' => 'FANSITE KUSTOM', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_logo', 'value' => 'app/logo-kustom.png', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Dashboard - FANSITE KUSTOM', false)
            ->assertSee('storage/app/logo-kustom.png', false)
            ->assertDontSee('/favicon.ico', false);
    }

    public function test_site_footer_shows_logo_with_fanbase_name_and_copyright_below(): void
    {
        DB::table('app_settings')->upsert([
            ['key' => 'app_name', 'value' => 'FANSITE KUSTOM', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_logo', 'value' => 'app/logo-kustom.png', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        DB::table('about_settings')->upsert([
            ['key' => 'fanbase_name', 'value' => 'Wota Nusantara', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        Cache::forget('app_settings');
        Cache::forget('about_settings');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('storage/app/logo-kustom.png', false)
            ->assertSee('Wota Nusantara')
            ->assertSee('©', false);
    }
}
