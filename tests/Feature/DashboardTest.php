<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_passes_required_view_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewHasAll([
            'idolName',
            'birthdayCountdown',
            'birthdayReminderActive',
            'stats',
            'period',
            'showComparison',
            'chartDates',
            'chartShowTeater',
            'chartKonser',
            'chartMeetGreet',
            'chartLiveStreaming',
            'totalShows',
            'nextMilestone',
            'milestoneProgress',
            'milestoneRemaining',
            'recentLiveStreaming',
        ]);
    }

    public function test_dashboard_period_filter_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach (['default', '7days', 'monthly', 'quarter', '6months', 'yearly'] as $period) {
            $response = $this->get(route('dashboard', ['period' => $period]));
            $response->assertOk();
            $response->assertViewHas('period', $period);
        }
    }

    public function test_dashboard_comparison_toggle_passes_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard', ['comparison' => '1']));
        $response->assertOk();
        $response->assertViewHas('showComparison', true);
        $response->assertViewHas('prevStats');
    }

    public function test_dashboard_caches_results(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Ensure cache is clear before the test
        Cache::flush();

        // Visit the dashboard
        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Assert that settings and stats are cached
        $this->assertTrue(Cache::has('about_settings'));
        $this->assertTrue(Cache::has('total_shows_count'));
    }
}
