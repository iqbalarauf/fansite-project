<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_error_page_renders_custom_design(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertStatus(404)
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertSee('404', false)
            ->assertSee('error-grid-bg', false)
            ->assertSee('/storage/app/error/404.svg', false)
            ->assertSee('/storage/app/error/404-dark.svg', false);
    }

    public function test_403_error_page_renders_custom_design(): void
    {
        $this->actingAs(User::factory()->contentCreator()->create());

        $this->get(route('photobooth.edit'))
            ->assertForbidden()
            ->assertSee('Akses Ditolak')
            ->assertSee('403', false)
            ->assertDontSee('/storage/app/error/404.svg', false);
    }

    public function test_500_error_page_renders_custom_design(): void
    {
        $this->actingAs(User::factory()->create());

        Route::get('_test/500', fn () => abort(500));

        $this->get('_test/500')
            ->assertStatus(500)
            ->assertSee('Terjadi Kesalahan')
            ->assertSee('Muat Ulang Halaman')
            ->assertSee('/storage/app/error/500.svg', false)
            ->assertSee('/storage/app/error/500-dark.svg', false);
    }

    public function test_error_page_footer_uses_app_name_from_settings(): void
    {
        DB::table('app_settings')->upsert([
            ['key' => 'app_name', 'value' => 'Fansite Keren', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        Cache::forget('app_settings');

        $this->get('/halaman-yang-tidak-ada')
            ->assertStatus(404)
            ->assertSee('Fansite Keren');
    }
}
