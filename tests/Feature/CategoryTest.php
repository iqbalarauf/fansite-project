<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('content.categories.index'))->assertOk();
    }

    public function test_category_can_be_created_for_news_and_blog(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('content.categories.store'), [
            'type' => 'news',
            'name' => 'Pengumuman',
            'slug' => 'pengumuman',
        ])->assertRedirect(route('content.categories.index', ['type' => 'news']));

        $this->post(route('content.categories.store'), [
            'type' => 'blog',
            'name' => 'Pribadi',
            'slug' => 'pribadi',
        ])->assertRedirect(route('content.categories.index', ['type' => 'blog']));

        $this->assertDatabaseHas('categories', ['type' => 'news', 'slug' => 'pengumuman']);
        $this->assertDatabaseHas('categories', ['type' => 'blog', 'slug' => 'pribadi']);
    }

    public function test_same_slug_is_allowed_across_types_but_not_within_a_type(): void
    {
        $this->actingAs(User::factory()->create());

        Category::query()->create(['type' => 'news', 'name' => 'Seru', 'slug' => 'seru']);

        $this->post(route('content.categories.store'), [
            'type' => 'news',
            'name' => 'Seru Lagi',
            'slug' => 'seru',
        ])->assertSessionHasErrors('slug');

        $this->post(route('content.categories.store'), [
            'type' => 'blog',
            'name' => 'Seru',
            'slug' => 'seru',
        ])->assertRedirect();

        $this->assertSame(2, Category::query()->where('slug', 'seru')->count());
    }

    public function test_category_can_be_updated_and_deleted(): void
    {
        $this->actingAs(User::factory()->create());

        $category = Category::query()->create(['type' => 'news', 'name' => 'Lama', 'slug' => 'lama']);

        $this->put(route('content.categories.update', $category->id), [
            'type' => 'news',
            'name' => 'Baru',
            'slug' => 'baru',
        ])->assertRedirect(route('content.categories.index', ['type' => 'news']));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Baru', 'slug' => 'baru']);

        $this->delete(route('content.categories.destroy', $category->id))
            ->assertRedirect(route('content.categories.index', ['type' => 'news']));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
