<?php

namespace Tests\Feature;

use App\Models\Magazine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MagazineTest extends TestCase
{
    use RefreshDatabase;

    public function test_magazine_list_page_is_displayed_for_content_roles(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $this->makeMagazine([
            'title' => 'Edisi Admin',
            'slug' => 'edisi-admin',
            'description' => 'Deskripsi admin',
            'cover' => 'magazines/covers/admin.jpg',
            'is_main' => true,
        ]);

        $this->get(route('magazines.index'))
            ->assertOk()
            ->assertSee('Edisi Admin')
            ->assertSee('Deskripsi admin')
            ->assertSee('Main Magazine');
    }

    public function test_magazine_can_be_created_with_pdf_cover_and_main_flag(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $existingMain = $this->makeMagazine(['slug' => 'edisi-lama', 'is_main' => true]);

        $this->post(route('magazines.store'), [
            'title' => 'Edisi Perdana',
            'slug' => 'edisi-perdana',
            'description' => 'Deskripsi edisi perdana',
            'cover' => UploadedFile::fake()->image('cover.jpg'),
            'file' => UploadedFile::fake()->create('edisi-perdana.pdf', 100, 'application/pdf'),
            'is_main' => 1,
        ])->assertRedirect(route('magazines.index'));

        $magazine = Magazine::query()->where('slug', 'edisi-perdana')->firstOrFail();

        $this->assertTrue($magazine->is_main);
        $this->assertSame('edisi-perdana.pdf', $magazine->original_name);
        $this->assertFalse($existingMain->fresh()->is_main);

        Storage::disk('public')->assertExists($magazine->file_path);
        Storage::disk('public')->assertExists($magazine->cover);
    }

    public function test_main_magazine_can_be_chosen(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $current = $this->makeMagazine(['slug' => 'edisi-satu', 'is_main' => true]);
        $next = $this->makeMagazine(['slug' => 'edisi-dua']);

        $this->post(route('magazines.set-main', $next))->assertRedirect(route('magazines.index'));

        $this->assertFalse($current->fresh()->is_main);
        $this->assertTrue($next->fresh()->is_main);
    }

    public function test_magazine_slug_and_description_can_be_updated(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $magazine = $this->makeMagazine();

        $this->put(route('magazines.update', $magazine), [
            'slug' => 'slug-baru',
            'description' => 'Deskripsi baru',
        ])->assertRedirect(route('magazines.index'));

        $magazine->refresh();

        $this->assertSame('slug-baru', $magazine->slug);
        $this->assertSame('Deskripsi baru', $magazine->description);
    }

    public function test_magazine_can_be_deleted_with_its_files(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $magazine = $this->makeMagazine(['cover' => 'magazines/covers/cover.jpg']);
        $cover = $magazine->cover;
        $file = $magazine->file_path;

        $this->delete(route('magazines.destroy', $magazine))->assertRedirect(route('magazines.index'));

        $this->assertSoftDeleted('magazines', ['id' => $magazine->id]);
        Storage::disk('public')->assertMissing($file);
        Storage::disk('public')->assertMissing($cover);
    }

    public function test_view_only_users_cannot_create_magazine(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->viewOnly()->create());

        $this->post(route('magazines.store'), [
            'title' => 'Edisi Perdana',
            'slug' => 'edisi-perdana',
            'file' => UploadedFile::fake()->create('edisi-perdana.pdf', 100, 'application/pdf'),
        ])->assertForbidden();

        $this->assertDatabaseMissing('magazines', ['slug' => 'edisi-perdana']);
    }

    public function test_public_magazine_page_displays_main_and_archive_editions(): void
    {
        Storage::fake('public');

        $this->makeMagazine(['title' => 'Edisi Utama', 'slug' => 'edisi-utama', 'is_main' => true]);
        $this->makeMagazine(['title' => 'Edisi Lama', 'slug' => 'edisi-lama']);

        $this->get(route('magazine.index'))
            ->assertOk()
            ->assertSee('Edisi Utama')
            ->assertSee('Edisi Lama')
            ->assertSee('Arsip Majalah');
    }

    public function test_viewing_a_magazine_increments_the_viewer_count(): void
    {
        Storage::fake('public');

        $magazine = $this->makeMagazine();

        $this->get(route('magazine.show', $magazine))->assertOk();

        $this->assertSame(1, $magazine->fresh()->views);
    }

    public function test_downloading_a_magazine_increments_the_download_count(): void
    {
        Storage::fake('public');

        $magazine = $this->makeMagazine();

        $this->get(route('magazine.download', $magazine))->assertOk();

        $this->assertSame(1, $magazine->fresh()->downloads);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeMagazine(array $attributes = []): Magazine
    {
        $magazine = Magazine::query()->create(array_merge([
            'title' => 'Edisi Perdana',
            'slug' => 'edisi-perdana',
            'description' => 'Deskripsi edisi perdana',
            'file_path' => 'magazines/files/edisi-perdana.pdf',
            'original_name' => 'Edisi Perdana.pdf',
        ], $attributes));

        Storage::disk('public')->put($magazine->file_path, 'dummy-pdf');

        if ($magazine->cover) {
            Storage::disk('public')->put($magazine->cover, 'dummy-cover');
        }

        return $magazine;
    }
}
