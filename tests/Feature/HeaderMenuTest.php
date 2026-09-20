<?php

namespace Tests\Feature;

use App\Models\CustomPage;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class HeaderMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_header_renders_exclusive_dropdown_hook(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-site-header', false);
    }

    public function test_default_mode_renders_the_default_navigation(): void
    {
        MenuItem::query()->create(['label' => 'Menu Rahasia', 'type' => 'link', 'url' => '/rahasia']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('schedule.index').'"', false)
            ->assertDontSee('Menu Rahasia');
    }

    public function test_custom_mode_renders_custom_items_and_submenus(): void
    {
        $parent = MenuItem::query()->create(['label' => 'Tentang', 'type' => 'link', 'url' => '/tentang']);
        MenuItem::query()->create(['label' => 'Sejarah', 'type' => 'link', 'url' => '/sejarah', 'parent_id' => $parent->id]);
        MenuItem::query()->create(['label' => 'YouTube', 'type' => 'link', 'url' => 'https://youtube.com/example']);

        $this->enableCustomMode();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tentang')
            ->assertSee('Sejarah')
            ->assertSee('https://youtube.com/example')
            ->assertDontSee('href="'.route('schedule.index').'"', false);
    }

    public function test_page_list_item_links_to_a_prebuilt_page(): void
    {
        MenuItem::query()->create(['label' => 'Jadwal', 'type' => 'page_list', 'target' => 'schedule']);
        MenuItem::query()->create(['label' => 'Profil Idol', 'type' => 'page_list', 'target' => 'about_idol']);

        $this->enableCustomMode();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Jadwal')
            ->assertSee('href="'.route('schedule.index').'"', false)
            ->assertSee('href="'.route('about.show').'"', false);
    }

    public function test_settings_page_previews_default_menu_and_custom_items(): void
    {
        $this->actingAs(User::factory()->create());

        MenuItem::query()->create(['label' => 'Menu Custom', 'type' => 'link', 'url' => '/custom']);

        Livewire::test('pages::header-menu.index')
            ->assertSee('Item Menu')
            ->assertSee('Galeri')
            ->set('mode', 'custom')
            ->assertSee('Menu Custom')
            ->assertDontSee('Galeri');
    }

    public function test_super_admin_can_create_a_list_page_item(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::header-menu.index')
            ->set('label', 'Jadwal')
            ->set('type', 'page_list')
            ->call('save')
            ->assertHasErrors('target')
            ->set('target', 'schedule')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('menu_items', ['label' => 'Jadwal', 'type' => 'page_list', 'target' => 'schedule']);
    }

    public function test_page_item_links_to_the_selected_custom_page(): void
    {
        $page = CustomPage::query()->create(['title' => 'Profil', 'slug' => 'profil', 'status' => 'published', 'blocks' => []]);

        MenuItem::query()->create(['label' => 'Profil', 'type' => 'page', 'page_id' => $page->id]);

        $this->enableCustomMode();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('custom-pages.show', 'profil').'"', false);
    }

    public function test_header_menu_settings_page_is_restricted_to_super_admin(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('header-menu.edit'))->assertOk();

        $this->actingAs(User::factory()->contentCreator()->create());
        $this->get(route('header-menu.edit'))->assertForbidden();
    }

    public function test_super_admin_can_manage_menu_items(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::header-menu.index')
            ->set('label', 'Kontak')
            ->set('type', 'link')
            ->set('url', 'https://contoh.test')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('menu_items', ['label' => 'Kontak', 'type' => 'link', 'url' => 'https://contoh.test']);

        $item = MenuItem::query()->where('label', 'Kontak')->firstOrFail();

        Livewire::test('pages::header-menu.index')
            ->call('delete', $item->id);

        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }

    public function test_deleting_a_parent_removes_its_children(): void
    {
        $parent = MenuItem::query()->create(['label' => 'Induk', 'type' => 'link', 'url' => '/induk']);
        $child = MenuItem::query()->create(['label' => 'Anak', 'type' => 'link', 'url' => '/anak', 'parent_id' => $parent->id]);

        $parent->delete();

        $this->assertDatabaseMissing('menu_items', ['id' => $child->id]);
    }

    public function test_item_cannot_be_its_own_parent(): void
    {
        $item = MenuItem::query()->create(['label' => 'Item', 'type' => 'link', 'url' => '/item']);

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::header-menu.index')
            ->call('edit', $item->id)
            ->set('label', 'Item')
            ->set('type', 'link')
            ->set('url', '/item')
            ->set('parentId', $item->id)
            ->call('save')
            ->assertHasErrors('parentId');

        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'parent_id' => null]);
    }

    public function test_group_item_renders_as_container_with_children(): void
    {
        $group = MenuItem::query()->create(['label' => 'Kategori', 'type' => 'group']);
        MenuItem::query()->create(['label' => 'Anak Satu', 'type' => 'link', 'url' => '/anak-satu', 'parent_id' => $group->id]);
        MenuItem::query()->create(['label' => 'Anak Dua', 'type' => 'link', 'url' => '/anak-dua', 'parent_id' => $group->id]);

        $this->enableCustomMode();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kategori')
            ->assertSee('Anak Satu')
            ->assertSee('Anak Dua');
    }

    public function test_empty_group_item_is_hidden(): void
    {
        MenuItem::query()->create(['label' => 'Grup Kosong', 'type' => 'group']);

        $this->enableCustomMode();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Grup Kosong');
    }

    public function test_group_requires_at_least_one_submenu_when_updated(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::header-menu.index')
            ->set('label', 'Kategori')
            ->set('type', 'group')
            ->call('save')
            ->assertHasNoErrors();

        $group = MenuItem::query()->where('label', 'Kategori')->firstOrFail();
        $this->assertSame('group', $group->type);

        Livewire::test('pages::header-menu.index')
            ->call('edit', $group->id)
            ->call('save')
            ->assertHasErrors('type');

        MenuItem::query()->create(['label' => 'Anak', 'type' => 'link', 'url' => '/anak', 'parent_id' => $group->id]);

        Livewire::test('pages::header-menu.index')
            ->call('edit', $group->id)
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_news_and_blog_items_are_hidden_when_features_are_disabled(): void
    {
        MenuItem::query()->create(['label' => 'Kanal Berita', 'type' => 'news']);
        MenuItem::query()->create(['label' => 'Catatan Blog', 'type' => 'blog']);
        MenuItem::query()->create(['label' => 'Halaman Majalah', 'type' => 'page_list', 'target' => 'magazines']);

        $this->enableCustomMode();

        DB::table('app_settings')->upsert([
            ['key' => 'news_enabled', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'blog_enabled', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'magazines_enabled', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        Cache::forget('app_settings');

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Kanal Berita')
            ->assertDontSee('Catatan Blog')
            ->assertDontSee('Halaman Majalah');
    }

    private function enableCustomMode(): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => 'header_menu_mode'],
            ['value' => 'custom', 'updated_at' => now()],
        );

        Cache::forget('app_settings');
    }
}
