<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Magazine;
use App\Models\NewsPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WelcomeFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_feed_source_shows_news(): void
    {
        NewsPost::query()->create([
            'title' => 'Berita Utama',
            'slug' => 'berita-utama',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Berita Terbaru')
            ->assertSee('Berita Utama');
    }

    public function test_feed_source_can_be_set_to_blog(): void
    {
        $this->setFeedSource('blog');

        BlogPost::query()->create([
            'title' => 'Tulisan Blog',
            'slug' => 'tulisan-blog',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        NewsPost::query()->create([
            'title' => 'Berita Lain',
            'slug' => 'berita-lain',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Blog Terbaru')
            ->assertSee('Tulisan Blog')
            ->assertDontSee('Berita Lain');
    }

    public function test_feed_source_can_be_set_to_magazines(): void
    {
        $this->setFeedSource('magazines');

        Magazine::query()->create([
            'title' => 'Majalah Edisi Satu',
            'slug' => 'majalah-edisi-satu',
            'file_path' => 'magazines/files/a.pdf',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Majalah Terbaru')
            ->assertSee('Majalah Edisi Satu');
    }

    public function test_feed_card_is_hidden_when_the_source_feature_is_disabled(): void
    {
        $this->setFeedSource('blog');

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'blog_enabled'],
            ['value' => 'false', 'updated_at' => now()],
        );
        Cache::forget('app_settings');

        BlogPost::query()->create([
            'title' => 'Tulisan Blog',
            'slug' => 'tulisan-blog',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Blog Terbaru')
            ->assertDontSee('Tulisan Blog');
    }

    private function setFeedSource(string $source): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => 'welcome_feed_source'],
            ['value' => $source, 'updated_at' => now()],
        );

        Cache::forget('app_settings');
    }
}
