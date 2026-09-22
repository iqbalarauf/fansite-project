<?php

namespace Tests\Feature;

use App\Models\GalleryPhoto;
use App\Models\NewsPost;
use App\Models\User;
use App\Support\SettingBag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_columns_are_set_on_creation_and_update(): void
    {
        $author = User::factory()->create();
        $editor = User::factory()->create();

        $this->actingAs($author);

        $post = NewsPost::query()->create([
            'title' => 'Berita Pertama',
            'slug' => 'berita-pertama',
            'status' => 'draft',
        ]);

        $this->assertSame($author->id, $post->created_by);
        $this->assertSame($author->id, $post->updated_by);

        $this->actingAs($editor);
        $post->update(['title' => 'Berita Revisi']);

        $this->assertSame($author->id, $post->fresh()->created_by);
        $this->assertSame($editor->id, $post->fresh()->updated_by);
    }

    public function test_setting_bag_exposes_typed_accessors(): void
    {
        DB::table('app_settings')->upsert([
            ['key' => 'news_enabled', 'value' => 'false'],
            ['key' => 'hero_button_1_link_value', 'value' => '#about'],
            ['key' => 'gallery_mode', 'value' => 'both'],
            ['key' => 'youtube_embed_enabled', 'value' => '1'],
        ], ['key'], ['value']);

        Cache::forget('app_settings');

        $this->assertFalse(SettingBag::bool('news_enabled', true));
        $this->assertTrue(SettingBag::bool('missing_flag', true));
        $this->assertTrue(SettingBag::bool('youtube_embed_enabled'));
        $this->assertSame('#about', SettingBag::string('hero_button_1_link_value'));
        $this->assertSame('fallback', SettingBag::string('missing_key', 'fallback'));
        $this->assertSame(0, SettingBag::int('missing_int'));
        $this->assertSame([], SettingBag::array('missing_array'));
        $this->assertFalse(SettingBag::featureEnabled('news'));
    }

    public function test_soft_deleted_gallery_photo_is_hidden_from_queries(): void
    {
        $photo = GalleryPhoto::query()->create([
            'photo' => 'gallery/photos/a.jpg',
            'description' => 'Foto Publik',
        ]);

        $this->assertSame(1, GalleryPhoto::query()->count());

        $photo->delete();

        $this->assertSame(0, GalleryPhoto::query()->count());
        $this->assertSame(1, GalleryPhoto::withTrashed()->count());
        $this->assertSoftDeleted('gallery_photos', ['id' => $photo->id]);
    }

    public function test_performance_indexes_exist(): void
    {
        $menuIndexes = collect(Schema::getIndexes('menu_items'))->pluck('columns')->flatten()->all();
        $this->assertContains('parent_id', $menuIndexes);
        $this->assertContains('sort_order', $menuIndexes);

        $postIndexes = collect(Schema::getIndexes('posts'))->pluck('columns')->flatten()->all();
        $this->assertContains('published_at', $postIndexes);

        $photoboothIndexes = collect(Schema::getIndexes('photobooths'))->pluck('columns')->flatten()->all();
        $this->assertContains('is_active', $photoboothIndexes);
    }
}
