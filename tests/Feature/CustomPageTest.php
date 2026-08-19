<?php

namespace Tests\Feature;

use App\Models\CustomPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_index_only_shows_the_page_list_and_create_button(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('pages.index'))
            ->assertOk()
            ->assertSee('Pages')
            ->assertSee('Tambah Halaman Baru')
            ->assertSee(route('pages.create'));
    }

    public function test_authenticated_user_can_save_and_publish_a_custom_page_with_a_generated_slug(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Profil Oshimen')
            ->set('blocks.0.data.background', 'accent')
            ->call('addBlock', 'text')
            ->set('blocks.1.data.text', 'Tentang halaman ini')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('profil-oshimen', $page->slug);
        $this->assertSame('published', $page->status);
        $this->assertCount(2, $page->blocks);
        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('Tentang halaman ini');
        $this->assertSame('/profil-oshimen', parse_url(route('custom-pages.show', $page), PHP_URL_PATH));
    }

    public function test_user_can_edit_and_delete_a_custom_page(): void
    {
        $user = User::factory()->create();
        $page = CustomPage::query()->create([
            'title' => 'Halaman Lama',
            'slug' => 'halaman-lama',
            'blocks' => [['id' => 'block-1', 'type' => 'text', 'data' => ['text' => 'Isi lama']]],
        ]);

        $this->actingAs($user)
            ->get(route('pages.edit', $page))
            ->assertOk()
            ->assertSee('Halaman Lama');

        $this->actingAs($user)
            ->delete(route('pages.destroy', $page))
            ->assertRedirect(route('pages.index'));

        $this->assertSoftDeleted($page);
        $this->get(route('pages.index'))->assertDontSee('Halaman Lama');
        $this->get(route('custom-pages.show', ['customPage' => 'halaman-lama']))->assertNotFound();
    }

    public function test_user_can_customize_the_slug_and_draft_pages_are_not_public(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Baru')
            ->set('slug', 'koleksi-spesial')
            ->call('save')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('koleksi-spesial', $page->slug);
        $this->get(route('custom-pages.show', $page))->assertNotFound();
    }
}
