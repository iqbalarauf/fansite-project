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
}
