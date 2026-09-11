<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContentFeatureToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_and_blog_are_enabled_by_default(): void
    {
        $this->get(route('news.index'))->assertOk();
        $this->get(route('blog.index'))->assertOk();
    }

    public function test_disabling_news_hides_its_public_page_and_navigation(): void
    {
        $this->setFeature('news', false);

        $this->get(route('news.index'))->assertNotFound();
        $this->get(route('blog.index'))->assertOk();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(route('news.index'), false)
            ->assertSee(route('blog.index'), false);
    }

    public function test_disabling_blog_rejects_admin_and_public_routes(): void
    {
        $this->setFeature('blog', false);

        $this->actingAs(User::factory()->create());

        $this->get(route('content.blog.index'))->assertNotFound();
        $this->get(route('content.news.index'))->assertOk();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('content.blog.index'), false)
            ->assertSee(route('content.news.index'), false);
    }

    public function test_categories_are_available_until_both_features_are_disabled(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('content.categories.index'))->assertOk();

        $this->setFeature('news', false);
        $this->setFeature('blog', false);

        $this->get(route('content.categories.index'))->assertNotFound();
    }

    public function test_disabling_magazines_hides_its_pages_and_menus(): void
    {
        $this->setFeature('magazines', false);

        $this->get(route('magazine.index'))->assertNotFound();

        $this->actingAs(User::factory()->create());

        $this->get(route('magazines.index'))->assertNotFound();
        $this->get(route('dashboard'))->assertOk()->assertDontSee(route('magazines.index'), false);
        $this->get(route('home'))->assertOk()->assertDontSee(route('magazine.index'), false);
    }

    private function setFeature(string $feature, bool $enabled): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => $feature.'_enabled'],
            ['value' => $enabled ? 'true' : 'false', 'updated_at' => now()],
        );

        Cache::forget('app_settings');
    }
}
