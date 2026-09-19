<?php

namespace Tests\Feature;

use App\Models\ShowTeater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_requires_authentication(): void
    {
        $this->get(route('admin.search', ['q' => 'test']))
            ->assertRedirect(route('login'));
    }

    public function test_short_query_returns_no_groups(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson(route('admin.search', ['q' => 'a']))
            ->assertOk()
            ->assertExactJson(['groups' => []]);
    }

    public function test_super_admin_can_search_show_teater(): void
    {
        ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => '2026-01-01',
            'setlist' => 'Pajama Drive',
        ]);

        $this->actingAs(User::factory()->create());

        $this->getJson(route('admin.search', ['q' => 'Pajama']))
            ->assertOk()
            ->assertJsonFragment(['label' => 'Pajama Drive'])
            ->assertJsonPath('groups.0.label', 'Master Data');
    }

    public function test_super_admin_can_search_users(): void
    {
        $admin = User::factory()->create(['name' => 'Zulkifli Testing']);

        $this->actingAs($admin);

        $this->getJson(route('admin.search', ['q' => 'Zulkifli']))
            ->assertOk()
            ->assertJsonFragment(['label' => 'Zulkifli Testing'])
            ->assertJsonFragment(['label' => 'User']);
    }

    public function test_content_creator_cannot_search_users(): void
    {
        $admin = User::factory()->create(['name' => 'Zulkifli Testing']);

        $this->actingAs(User::factory()->contentCreator()->create());

        $this->getJson(route('admin.search', ['q' => 'Zulkifli']))
            ->assertOk()
            ->assertJsonMissing(['label' => 'Zulkifli Testing']);
    }

    public function test_search_includes_navigation_menu_items(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson(route('admin.search', ['q' => 'Photobooth']))
            ->assertOk()
            ->assertJsonFragment(['label' => 'Photobooth'])
            ->assertJsonPath('groups.0.label', 'Menu');
    }

    public function test_search_returns_empty_groups_when_nothing_matches(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson(route('admin.search', ['q' => 'zzzznomatch']))
            ->assertOk()
            ->assertExactJson(['groups' => []]);
    }
}
