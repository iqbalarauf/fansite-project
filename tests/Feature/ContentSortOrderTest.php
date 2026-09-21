<?php

namespace Tests\Feature;

use App\Models\GalleryPhoto;
use App\Models\GalleryVideo;
use App\Models\Timeline;
use App\Models\Trivia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentSortOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_photo_sort_order_is_saved_on_store_and_update(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->post(route('content.gallery.photos.store'), [
            'photo' => UploadedFile::fake()->image('photo.jpg'),
            'description' => 'Foto',
            'sort_order' => 5,
        ])->assertRedirect();

        $photo = GalleryPhoto::query()->firstOrFail();
        $this->assertSame(5, $photo->sort_order);

        $this->put(route('content.gallery.photos.update', $photo), [
            'description' => 'Foto',
            'sort_order' => 2,
        ])->assertRedirect();

        $this->assertSame(2, $photo->fresh()->sort_order);
    }

    public function test_gallery_video_sort_order_is_saved_on_store(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('content.gallery.videos.store'), [
            'url' => 'https://youtu.be/abcdefghijk',
            'title' => 'Video',
            'sort_order' => 3,
        ])->assertRedirect();

        $this->assertSame(3, GalleryVideo::query()->firstOrFail()->sort_order);
    }

    public function test_timeline_sort_order_is_saved_on_store_and_update(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('content.timeline.store'), [
            'date' => '2026-01-01',
            'description' => 'Timeline',
            'sort_order' => 4,
        ])->assertRedirect();

        $timeline = Timeline::query()->firstOrFail();
        $this->assertSame(4, $timeline->sort_order);

        $this->put(route('content.timeline.update', $timeline), [
            'date' => '2026-01-01',
            'description' => 'Timeline',
            'sort_order' => 1,
        ])->assertRedirect();

        $this->assertSame(1, $timeline->fresh()->sort_order);
    }

    public function test_trivia_sort_order_is_saved_on_store_and_update(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('content.trivia.store'), [
            'title' => 'Trivia',
            'description' => 'Isi',
            'sort_order' => 7,
        ])->assertRedirect();

        $trivia = Trivia::query()->firstOrFail();
        $this->assertSame(7, $trivia->sort_order);

        $this->put(route('content.trivia.update', $trivia), [
            'title' => 'Trivia',
            'description' => 'Isi',
            'sort_order' => 0,
        ])->assertRedirect();

        $this->assertSame(0, $trivia->fresh()->sort_order);
    }
}
