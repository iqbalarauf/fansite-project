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
            'eventDisplayMode',
            'eventDisplayLimit',
            'chartDates',
            'chartShowTeater',
            'chartKonser',
            'chartMeetGreet',
            'chartLiveStreaming',
            'totalShows',
            'nextMilestone',
            'milestoneProgress',
            'milestoneRemaining',
            'liveStreamingEvents',
            'pastEvents',
            'upcomingEvents',
        ]);
    }

    public function test_dashboard_period_filter_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach (['7days', 'monthly', 'quarter', '6months', 'yearly'] as $period) {
            $response = $this->get(route('dashboard', ['period' => $period]));
            $response->assertOk();
            $response->assertViewHas('period', $period);
        }

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewHas('period', '7days');
        $response->assertDontSee('Default');
        $response->assertSee('7 Hari', false);
    }

    public function test_dashboard_event_display_controls_pass_through(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard', [
            'event_display_mode' => 'count',
            'event_display_limit' => 10,
        ]));

        $response->assertOk();
        $response->assertViewHas('eventDisplayMode', 'count');
        $response->assertViewHas('eventDisplayLimit', 10);
        $response->assertSee('Berdasarkan jumlah event', false);
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
