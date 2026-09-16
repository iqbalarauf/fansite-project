<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('404', false);
    }

    public function test_403_error_page_renders_custom_design(): void
    {
        $this->actingAs(User::factory()->contentCreator()->create());

        $this->get(route('photobooth.edit'))
            ->assertForbidden()
            ->assertSee('Akses Ditolak')
            ->assertSee('403', false);
    }

    public function test_500_error_page_renders_custom_design(): void
    {
        $this->actingAs(User::factory()->create());

        Route::get('_test/500', fn () => abort(500));

        $this->get('_test/500')
            ->assertStatus(500)
            ->assertSee('Terjadi Kesalahan')
            ->assertSee('Muat Ulang Halaman');
    }
}
