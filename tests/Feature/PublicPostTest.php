<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\NewsPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_news_lists_only_published_posts(): void
    {
        Storage::fake('public');

        $category = Category::query()->create(['type' => 'news', 'name' => 'Pengumuman', 'slug' => 'pengumuman']);

        NewsPost::query()->create([
            'category_id' => $category->id,
            'title' => 'Berita Terbit',
            'slug' => 'berita-terbit',
            'content' => '<p>Konten terbit</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        NewsPost::query()->create([
            'title' => 'Berita Draft',
            'slug' => 'berita-draft',
            'status' => 'draft',
        ]);

        NewsPost::query()->create([
            'title' => 'Berita Terjadwal',
            'slug' => 'berita-terjadwal',
            'status' => 'published',
            'published_at' => now()->addWeek(),
        ]);

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('Berita Terbit')
            ->assertSee('Pengumuman')
            ->assertDontSee('Berita Draft')
            ->assertDontSee('Berita Terjadwal');
    }

    public function test_public_news_detail_shows_content_and_hides_drafts(): void
    {
        Storage::fake('public');

        NewsPost::query()->create([
            'title' => 'Berita Detail',
            'slug' => 'berita-detail',
            'content' => '<p>Isi lengkap berita</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        NewsPost::query()->create([
            'title' => 'Berita Draft Detail',
            'slug' => 'berita-draft-detail',
            'status' => 'draft',
        ]);

        $this->get(route('news.show', 'berita-detail'))
            ->assertOk()
            ->assertSee('Isi lengkap berita');

        $this->get(route('news.show', 'berita-draft-detail'))->assertNotFound();
    }

    public function test_public_blog_pages_work(): void
    {
        Storage::fake('public');

        BlogPost::query()->create([
            'title' => 'Tulisan Blog',
            'slug' => 'tulisan-blog',
            'content' => '<p>Isi blog</p>',
            'status' => 'published',
            'is_featured' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Tulisan Blog')
            ->assertSee('Sorotan');

        $this->get(route('blog.show', 'tulisan-blog'))
            ->assertOk()
            ->assertSee('Isi blog');
    }
}
