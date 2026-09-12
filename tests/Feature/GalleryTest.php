<?php

namespace Tests\Feature;

use App\Models\GalleryPhoto;
use App\Models\GalleryVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gallery_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('content.gallery.index'))
            ->assertOk()
            ->assertSee('Galeri');
    }

    public function test_photo_can_be_created_updated_and_deleted(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->post(route('content.gallery.photos.store'), [
            'photo' => UploadedFile::fake()->image('photo.jpg'),
            'description' => 'Foto konser',
            'credit_photographer' => 'Budi',
        ])->assertRedirect(route('content.gallery.index', ['tab' => 'photos']));

        $photo = GalleryPhoto::query()->firstOrFail();
        Storage::disk('public')->assertExists($photo->photo);
        $this->assertSame('Budi', $photo->credit_photographer);

        $this->put(route('content.gallery.photos.update', $photo), [
            'description' => 'Foto konser terbaru',
            'credit_photographer' => 'Andi',
        ])->assertRedirect(route('content.gallery.index', ['tab' => 'photos']));

        $photo->refresh();
        $this->assertSame('Foto konser terbaru', $photo->description);
        $this->assertSame('Andi', $photo->credit_photographer);

        $path = $photo->photo;

        $this->delete(route('content.gallery.photos.destroy', $photo))
            ->assertRedirect(route('content.gallery.index', ['tab' => 'photos']));

        $this->assertDatabaseMissing('gallery_photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_video_can_be_created_and_rejects_unsupported_urls(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('content.gallery.videos.store'), [
            'url' => 'https://www.youtube.com/watch?v=abcdefghijk',
            'title' => 'Live Showroom',
            'credit_account' => '@fansite',
        ])->assertRedirect(route('content.gallery.index', ['tab' => 'videos']));

        $this->assertDatabaseHas('gallery_videos', [
            'platform' => 'youtube',
            'title' => 'Live Showroom',
            'credit_account' => '@fansite',
        ]);

        $this->post(route('content.gallery.videos.store'), [
            'url' => 'https://example.com/not-a-video',
        ])->assertSessionHasErrors('url');
    }

    public function test_public_gallery_mode_photos_shows_only_photos(): void
    {
        $this->setMode('photos');
        GalleryPhoto::query()->create(['photo' => 'gallery/photos/a.jpg', 'description' => 'Foto A']);
        GalleryVideo::query()->create(['platform' => 'youtube', 'url' => 'https://youtu.be/abcdefghijk', 'title' => 'Video A']);

        $this->get(route('gallery.index'))
            ->assertOk()
            ->assertSee('Foto A')
            ->assertDontSee('Video A');
    }

    public function test_public_gallery_mode_videos_shows_only_videos(): void
    {
        $this->setMode('videos');
        GalleryPhoto::query()->create(['photo' => 'gallery/photos/a.jpg', 'description' => 'Foto A']);
        GalleryVideo::query()->create(['platform' => 'youtube', 'url' => 'https://youtu.be/abcdefghijk', 'title' => 'Video A']);

        $this->get(route('gallery.index'))
            ->assertOk()
            ->assertSee('Video A')
            ->assertDontSee('Foto A');
    }

    public function test_public_gallery_mode_both_shows_photos_and_videos(): void
    {
        $this->setMode('both');
        GalleryPhoto::query()->create(['photo' => 'gallery/photos/a.jpg', 'description' => 'Foto A']);
        GalleryVideo::query()->create(['platform' => 'youtube', 'url' => 'https://youtu.be/abcdefghijk', 'title' => 'Video A']);

        $this->get(route('gallery.index'))
            ->assertOk()
            ->assertSee('Foto A')
            ->assertSee('Video A');
    }

    public function test_welcome_shows_latest_gallery_photos_when_photos_enabled(): void
    {
        $this->setMode('photos');
        GalleryPhoto::query()->create(['photo' => 'gallery/photos/a.jpg', 'description' => 'Foto Welcome']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Foto Terbaru')
            ->assertSee('Foto Welcome')
            ->assertSee('data-gallery-carousel', false)
            ->assertSee('data-gallery-track', false);
    }

    public function test_welcome_hides_gallery_when_mode_is_videos(): void
    {
        $this->setMode('videos');
        GalleryPhoto::query()->create(['photo' => 'gallery/photos/a.jpg', 'description' => 'Foto Welcome']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Foto Welcome');
    }

    private function setMode(string $mode): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => 'gallery_mode'],
            ['value' => $mode, 'updated_at' => now()],
        );

        Cache::forget('app_settings');
    }
}
