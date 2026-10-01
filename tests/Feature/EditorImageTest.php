<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditorImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_upload_editor_image(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $response = $this->post(route('content.editor.image'), [
            'image' => UploadedFile::fake()->image('inline.jpg'),
        ]);

        $response->assertOk()->assertJsonStructure(['url']);

        $this->assertStringContainsString('/storage/content/editor/', (string) $response->json('url'));
    }

    public function test_content_creator_can_upload_editor_image(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->contentCreator()->create());

        $this->post(route('content.editor.image'), [
            'image' => UploadedFile::fake()->image('inline.png'),
        ])->assertOk()->assertJsonStructure(['url']);
    }

    public function test_editor_image_upload_rejects_non_image(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $this->postJson(route('content.editor.image'), [
            'image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ])->assertStatus(422)->assertJsonValidationErrors('image');
    }

    public function test_view_only_cannot_upload_editor_image(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->viewOnly()->create());

        $this->post(route('content.editor.image'), [
            'image' => UploadedFile::fake()->image('inline.jpg'),
        ])->assertForbidden();
    }

    public function test_public_disk_url_is_relative_when_app_url_is_unset(): void
    {
        config(['app.url' => null, 'filesystems.disks.public.url' => '/storage']);

        $this->assertSame('/storage/content/editor/foto.webp', Storage::disk('public')->url('content/editor/foto.webp'));
    }

    public function test_editor_image_upload_returns_a_usable_relative_url(): void
    {
        Storage::fake('public');

        config(['filesystems.disks.public.url' => '/storage']);

        $this->actingAs(User::factory()->create());

        $response = $this->post(route('content.editor.image'), [
            'image' => UploadedFile::fake()->image('inline.jpg'),
        ]);

        $response->assertOk();

        $url = (string) $response->json('url');

        // Relative URL follows the request host instead of a hardcoded APP_URL.
        $this->assertStringStartsWith('/storage/content/editor/', $url);
    }

    public function test_uploaded_editor_image_actually_exists_on_disk(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $response = $this->post(route('content.editor.image'), [
            'image' => UploadedFile::fake()->image('inline.jpg'),
        ]);

        $response->assertOk();

        $path = str_replace('/storage/', '', (string) $response->json('url'));

        Storage::disk('public')->assertExists($path);
    }

    public function test_upload_fails_with_500_when_storage_cannot_be_written(): void
    {
        // Force the write to fail regardless of platform by pointing the disk root at a file.
        $blockingFile = tempnam(sys_get_temp_dir(), 'diskroot');
        file_put_contents($blockingFile, 'not-a-directory');

        config([
            'filesystems.disks.public.root' => $blockingFile,
            'filesystems.disks.public.throw' => true,
        ]);

        $this->actingAs(User::factory()->create());

        $response = $this->postJson(route('content.editor.image'), [
            'image' => UploadedFile::fake()->image('inline.jpg'),
        ]);

        @unlink($blockingFile);

        $response->assertStatus(500)->assertJsonStructure(['message']);
    }
}
