<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShowTeaterSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejecting_a_show_soft_deletes_it(): void
    {
        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => '2026/06/22',
            'setlist' => 'Pajama Drive',
            'is_member_show' => 0,
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('show-teater.reject', 1))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSoftDeleted('show_teater', ['show_id' => 1]);
    }

    public function test_soft_deleted_shows_are_hidden_from_the_index(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026/06/22', 'setlist' => 'Visible Setlist', 'deleted_at' => null],
            ['show_id' => 2, 'show_date' => '2026/06/23', 'setlist' => 'Deleted Setlist', 'deleted_at' => now()],
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('show-teater.index'))
            ->assertOk()
            ->assertSee('Visible Setlist')
            ->assertDontSee('Deleted Setlist');
    }

    public function test_storing_with_a_soft_deleted_show_id_restores_it(): void
    {
        DB::table('show_teater')->insert([
            'show_id' => 5,
            'show_date' => '2026/01/01',
            'setlist' => 'Old Setlist',
            'deleted_at' => now(),
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('show-teater.store'), [
                'show_id' => 5,
                'show_date' => '2026-06-22',
                'setlist' => 'New Setlist',
            ])
            ->assertRedirect(route('show-teater.index'));

        $this->assertDatabaseHas('show_teater', [
            'show_id' => 5,
            'setlist' => 'New Setlist',
            'deleted_at' => null,
        ]);
        $this->assertSame(1, DB::table('show_teater')->where('show_id', 5)->count());
    }

    public function test_storing_an_existing_active_show_id_is_rejected(): void
    {
        DB::table('show_teater')->insert([
            'show_id' => 7,
            'show_date' => '2026/06/22',
            'setlist' => 'Existing',
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('show-teater.store'), [
                'show_id' => 7,
                'show_date' => '2026-06-23',
                'setlist' => 'Duplicate',
            ])
            ->assertSessionHasErrors('show_id');

        $this->assertSame(1, DB::table('show_teater')->where('show_id', 7)->count());
    }

    public function test_dashboard_stats_exclude_soft_deleted_shows(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026/06/22', 'setlist' => 'Active', 'deleted_at' => null],
            ['show_id' => 2, 'show_date' => '2026/06/23', 'setlist' => 'Deleted', 'deleted_at' => now()],
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk();
        $this->assertSame(1, $response->viewData('stats')['total_shows']);
    }
}
