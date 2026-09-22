<?php

namespace Tests\Feature;

use App\Models\CustomPage;
use App\Models\Magazine;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ContentPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_only_can_read_but_cannot_manage_content(): void
    {
        $author = User::factory()->contentCreator()->create();

        $this->actingAs($author);
        $post = NewsPost::query()->create([
            'title' => 'Berita',
            'slug' => 'berita',
            'status' => 'draft',
        ]);

        $viewer = User::factory()->viewOnly()->create();

        $this->assertTrue(Gate::forUser($viewer)->allows('view', $post));
        $this->assertFalse(Gate::forUser($viewer)->allows('create', NewsPost::class));
        $this->assertFalse(Gate::forUser($viewer)->allows('update', $post));
        $this->assertFalse(Gate::forUser($viewer)->allows('delete', $post));
    }

    public function test_bank_data_admin_cannot_access_content_policies(): void
    {
        $post = $this->unclaimedPost();
        $bankDataAdmin = User::factory()->bankDataAdmin()->create();

        $this->assertFalse(Gate::forUser($bankDataAdmin)->allows('view', $post));
        $this->assertFalse(Gate::forUser($bankDataAdmin)->allows('create', NewsPost::class));
        $this->assertFalse(Gate::forUser($bankDataAdmin)->allows('update', $post));
    }

    public function test_super_admin_can_manage_any_content(): void
    {
        $owner = User::factory()->contentCreator()->create();

        $this->actingAs($owner);
        $post = NewsPost::query()->create([
            'title' => 'Berita',
            'slug' => 'berita',
            'status' => 'draft',
        ]);

        $admin = User::factory()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('update', $post));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $post));
        $this->assertTrue(Gate::forUser($admin)->allows('forceDelete', $post));
    }

    public function test_content_creator_can_only_modify_own_or_unclaimed_content(): void
    {
        $creator = User::factory()->contentCreator()->create();
        $otherCreator = User::factory()->contentCreator()->create();

        $this->actingAs($creator);
        $post = NewsPost::query()->create([
            'title' => 'Berita',
            'slug' => 'berita',
            'status' => 'draft',
        ]);

        $this->assertSame($creator->id, $post->created_by);

        $this->assertTrue(Gate::forUser($creator)->allows('create', NewsPost::class));
        $this->assertTrue(Gate::forUser($creator)->allows('update', $post));
        $this->assertFalse(Gate::forUser($otherCreator)->allows('update', $post));
        $this->assertFalse(Gate::forUser($otherCreator)->allows('delete', $post));
        $this->assertTrue(Gate::forUser($otherCreator)->allows('update', $this->unclaimedPost()));
    }

    public function test_only_super_admin_can_restore_or_force_delete_content(): void
    {
        $post = $this->unclaimedPost();
        $creator = User::factory()->contentCreator()->create();

        $this->assertFalse(Gate::forUser($creator)->allows('restore', $post));
        $this->assertFalse(Gate::forUser($creator)->allows('forceDelete', $post));
        $this->assertTrue(Gate::forUser(User::factory()->create())->allows('restore', $post));
    }

    public function test_magazine_controller_blocks_content_creator_from_modifying_others_magazine(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner);
        $magazine = Magazine::query()->create([
            'title' => 'Majalah',
            'slug' => 'majalah',
            'file_path' => 'magazines/files/majalah.pdf',
            'original_name' => 'majalah.pdf',
        ]);

        $creator = User::factory()->contentCreator()->create();

        $this->actingAs($creator)
            ->put(route('magazines.update', $magazine), ['slug' => 'majalah-baru'])
            ->assertForbidden();

        $this->actingAs($creator)
            ->delete(route('magazines.destroy', $magazine))
            ->assertForbidden();
    }

    public function test_magazine_controller_allows_creator_to_modify_own_magazine(): void
    {
        $creator = User::factory()->contentCreator()->create();

        $this->actingAs($creator);
        $magazine = Magazine::query()->create([
            'title' => 'Majalah',
            'slug' => 'majalah',
            'file_path' => 'magazines/files/majalah.pdf',
            'original_name' => 'majalah.pdf',
        ]);

        $this->actingAs($creator)
            ->put(route('magazines.update', $magazine), ['slug' => 'majalah-baru'])
            ->assertRedirect(route('magazines.index'));

        $this->assertSame('majalah-baru', $magazine->fresh()->slug);
    }

    public function test_custom_page_controller_blocks_content_creator_from_deleting_others_page(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner);
        $page = CustomPage::query()->create([
            'title' => 'Halaman',
            'slug' => 'halaman',
            'blocks' => [],
        ]);

        $creator = User::factory()->contentCreator()->create();

        $this->actingAs($creator)
            ->delete(route('pages.destroy', $page))
            ->assertForbidden();

        $this->assertNotSoftDeleted($page);
    }

    private function unclaimedPost(): NewsPost
    {
        return new NewsPost([
            'title' => 'Tanpa Pemilik',
            'slug' => 'tanpa-pemilik',
            'status' => 'draft',
        ]);
    }
}
