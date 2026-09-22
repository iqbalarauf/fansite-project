<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_and_blog_list_pages_are_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('content.news.index'))->assertOk();
        $this->get(route('content.blog.index'))->assertOk();
    }

    public function test_post_create_and_edit_forms_are_displayed(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $this->get(route('content.news.create'))->assertOk()->assertSee('data-rich-text', false);

        $this->post(route('content.blog.store'), [
            'title' => 'Untuk Edit',
            'slug' => 'untuk-edit',
            'status' => 'draft',
        ]);

        $id = DB::table('posts')->where('type', 'blog')->where('slug', 'untuk-edit')->value('id');

        $this->get(route('content.blog.edit', $id))->assertOk()->assertSee('data-rich-text', false);
    }

    public function test_news_and_blog_posts_are_stored_with_type_in_a_single_table(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $newsCategory = Category::query()->create(['type' => 'news', 'name' => 'Pengumuman', 'slug' => 'pengumuman']);
        $blogCategory = Category::query()->create(['type' => 'blog', 'name' => 'Ulasan', 'slug' => 'ulasan']);

        $this->post(route('content.news.store'), [
            'title' => 'Berita Utama',
            'slug' => 'berita-utama',
            'excerpt' => 'Ringkasan berita',
            'content' => '<p>Isi berita</p>',
            'status' => 'published',
            'category_id' => $newsCategory->id,
        ])->assertRedirect(route('content.news.index'));

        $this->post(route('content.blog.store'), [
            'title' => 'Tulisan Blog',
            'slug' => 'tulisan-blog',
            'content' => '<p>Isi blog</p>',
            'status' => 'draft',
            'category_id' => $blogCategory->id,
        ])->assertRedirect(route('content.blog.index'));

        $this->assertDatabaseHas('posts', ['type' => 'news', 'slug' => 'berita-utama', 'title' => 'Berita Utama']);
        $this->assertDatabaseHas('posts', ['type' => 'blog', 'slug' => 'tulisan-blog', 'title' => 'Tulisan Blog']);
        $this->assertDatabaseMissing('posts', ['type' => 'news', 'slug' => 'tulisan-blog']);
        $this->assertDatabaseMissing('posts', ['type' => 'blog', 'slug' => 'berita-utama']);
    }

    public function test_publishing_without_date_sets_published_at(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $this->post(route('content.news.store'), [
            'title' => 'Tanpa Tanggal',
            'slug' => 'tanpa-tanggal',
            'status' => 'published',
        ])->assertRedirect(route('content.news.index'));

        $this->assertNotNull(DB::table('posts')->where('type', 'news')->where('slug', 'tanpa-tanggal')->value('published_at'));
    }

    public function test_post_can_be_updated_and_featured(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $this->post(route('content.blog.store'), [
            'title' => 'Judul Awal',
            'slug' => 'judul-awal',
            'status' => 'draft',
        ]);

        $id = DB::table('posts')->where('type', 'blog')->where('slug', 'judul-awal')->value('id');

        $this->put(route('content.blog.update', $id), [
            'title' => 'Judul Baru',
            'slug' => 'judul-baru',
            'status' => 'published',
            'is_featured' => 1,
        ])->assertRedirect(route('content.blog.index'));

        $this->assertDatabaseHas('posts', [
            'id' => $id,
            'type' => 'blog',
            'title' => 'Judul Baru',
            'slug' => 'judul-baru',
            'status' => 'published',
            'is_featured' => 1,
        ]);
    }

    public function test_post_can_be_deleted(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $this->post(route('content.news.store'), [
            'title' => 'Akan Dihapus',
            'slug' => 'akan-dihapus',
            'status' => 'draft',
        ]);

        $id = DB::table('posts')->where('type', 'news')->where('slug', 'akan-dihapus')->value('id');

        $this->delete(route('content.news.destroy', $id))->assertRedirect(route('content.news.index'));

        $this->assertSoftDeleted('posts', ['id' => $id]);
    }

    public function test_slug_must_be_unique_within_a_table_but_can_repeat_across_tables(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $payload = ['title' => 'Sama', 'slug' => 'slug-sama', 'status' => 'draft'];

        $this->post(route('content.news.store'), $payload)->assertRedirect();
        $this->post(route('content.blog.store'), $payload)->assertRedirect();

        $this->post(route('content.news.store'), $payload)->assertSessionHasErrors('slug');
    }

    public function test_view_only_users_cannot_create_posts(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->viewOnly()->create());

        $this->post(route('content.news.store'), [
            'title' => 'Tidak Boleh',
            'slug' => 'tidak-boleh',
            'status' => 'draft',
        ])->assertForbidden();
    }
}
