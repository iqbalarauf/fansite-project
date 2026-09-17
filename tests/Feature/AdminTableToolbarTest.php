<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomPage;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTableToolbarTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_teater_table_displays_datatable_toolbar(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('show-teater.index'))
            ->assertOk()
            ->assertSee('name="per_page"', false)
            ->assertSee('name="search"', false)
            ->assertSee('entries')
            ->assertSee('Filter')
            ->assertSee('Showing');
    }

    public function test_timeline_table_displays_toolbar_and_search_filters_rows(): void
    {
        Timeline::query()->create(['date' => '2026-01-01', 'description' => 'Konser Perdana Jakarta']);
        Timeline::query()->create(['date' => '2026-02-02', 'description' => 'Meet and Greet Bandung']);

        $this->actingAs(User::factory()->create());

        $this->get(route('content.timeline.index'))
            ->assertOk()
            ->assertSee('name="search"', false)
            ->assertSee('entries');

        $this->get(route('content.timeline.index', ['search' => 'Perdana']))
            ->assertOk()
            ->assertSee('Konser Perdana Jakarta')
            ->assertDontSee('Meet and Greet Bandung');
    }

    public function test_custom_pages_table_search_filters_rows(): void
    {
        CustomPage::query()->create(['title' => 'Halaman Profil', 'slug' => 'profil', 'status' => 'published', 'blocks' => []]);
        CustomPage::query()->create(['title' => 'Halaman Kontak', 'slug' => 'kontak', 'status' => 'published', 'blocks' => []]);

        $this->actingAs(User::factory()->create());

        $this->get(route('pages.index', ['search' => 'Profil']))
            ->assertOk()
            ->assertSee('Halaman Profil')
            ->assertDontSee('Halaman Kontak');
    }

    public function test_category_table_search_filters_rows(): void
    {
        Category::query()->create(['type' => 'news', 'name' => 'Pengumuman', 'slug' => 'pengumuman']);
        Category::query()->create(['type' => 'news', 'name' => 'Liputan', 'slug' => 'liputan']);

        $this->actingAs(User::factory()->create());

        $this->get(route('content.categories.index', ['search' => 'Pengumuman']))
            ->assertOk()
            ->assertSee('Pengumuman')
            ->assertDontSee('Liputan');
    }

    public function test_users_table_search_filters_rows(): void
    {
        User::factory()->create(['name' => 'Nabila Kurnia']);
        User::factory()->create(['name' => 'Rangga Pratama']);

        $this->actingAs(User::factory()->create(['name' => 'Admin Utama']));

        $this->get(route('users.index', ['search' => 'Nabila']))
            ->assertOk()
            ->assertSee('Nabila Kurnia')
            ->assertDontSee('Rangga Pratama');
    }
}
