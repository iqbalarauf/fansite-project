<?php

namespace Tests\Feature;

use App\Models\ShowTeaterCategories;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTeaterCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_renders_setlists_and_unit_songs(): void
    {
        $setlist = ShowTeaterCategories::factory()->create(['name' => 'Pajama Drive']);
        ShowTeaterCategories::factory()->unitSong($setlist)->create(['name' => 'Tenshi no Shippo']);

        $this->actingAs(User::factory()->create());

        $this->get(route('show-teater.categories.index'))
            ->assertOk()
            ->assertSee('Pajama Drive');

        $this->get(route('show-teater.categories.index', ['tab' => 'unit_song']))
            ->assertOk()
            ->assertSee('Tenshi no Shippo')
            ->assertSee('Pajama Drive');
    }

    public function test_store_creates_a_category_and_rejects_duplicates(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('show-teater.categories.store'), [
            'type' => 'setlist',
            'name' => 'Pajama Drive',
            'jp_name' => 'パジャマドライブ',
        ])->assertRedirect();

        $this->assertDatabaseHas('show_teater_categories', [
            'type' => 'setlist',
            'name' => 'Pajama Drive',
            'is_active' => 1,
        ]);

        $this->post(route('show-teater.categories.store'), [
            'type' => 'setlist',
            'name' => 'Pajama Drive',
        ])->assertSessionHasErrors('name');
    }

    public function test_update_renames_a_category_and_rejects_duplicates(): void
    {
        $this->actingAs(User::factory()->create());

        $setlist = ShowTeaterCategories::factory()->create(['name' => 'Pajama Drive']);
        ShowTeaterCategories::factory()->create(['name' => 'Renai Kinshi Jourei']);

        $this->put(route('show-teater.categories.update', $setlist->id), [
            'name' => 'Pajama Drive 2',
            'jp_name' => null,
        ])->assertRedirect();

        $this->assertDatabaseHas('show_teater_categories', ['id' => $setlist->id, 'name' => 'Pajama Drive 2']);

        $this->put(route('show-teater.categories.update', $setlist->id), [
            'name' => 'Renai Kinshi Jourei',
        ])->assertSessionHasErrors('name');
    }

    public function test_deactivating_a_setlist_also_deactivates_its_unit_songs(): void
    {
        $this->actingAs(User::factory()->create());

        $setlist = ShowTeaterCategories::factory()->create(['name' => 'Pajama Drive']);
        $unitSong = ShowTeaterCategories::factory()->unitSong($setlist)->create(['name' => 'Tenshi no Shippo']);

        $this->post(route('show-teater.categories.toggle-status', $setlist->id))
            ->assertOk()
            ->assertJson(['success' => true, 'is_active' => 0]);

        $this->assertFalse($setlist->fresh()->is_active);
        $this->assertFalse($unitSong->fresh()->is_active);
    }

    public function test_toggling_a_unit_song_keeps_the_setlist_active(): void
    {
        $this->actingAs(User::factory()->create());

        $setlist = ShowTeaterCategories::factory()->create(['name' => 'Pajama Drive']);
        $unitSong = ShowTeaterCategories::factory()->unitSong($setlist)->create(['name' => 'Tenshi no Shippo']);

        $this->post(route('show-teater.categories.toggle-status', $unitSong->id))
            ->assertOk()
            ->assertJson(['success' => true, 'is_active' => 0]);

        $this->assertFalse($unitSong->fresh()->is_active);
        $this->assertTrue($setlist->fresh()->is_active);
    }
}
