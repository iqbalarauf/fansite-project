<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        foreach (['all', '7days', 'monthly', 'quarter', '6months', 'yearly'] as $period) {
            $response = $this->get(route('dashboard', ['period' => $period]));
            $response->assertOk();
            $response->assertViewHas('period', $period);
        }

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewHas('period', 'all');
        $response->assertSee('All', false);
        $response->assertDontSee('Mode Event', false);
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

    public function test_default_dashboard_timelines_include_all_event_sources_before_and_after_today(): void
    {
        $today = now()->toDateString();

        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => now()->subDay()->toDateString(), 'setlist' => 'Past Show'],
            ['show_id' => 2, 'show_date' => now()->addDay()->toDateString(), 'setlist' => 'Upcoming Show'],
            ['show_id' => 3, 'show_date' => $today, 'setlist' => 'Today Show'],
        ]);
        DB::table('concert_events')->insert([
            ['event_name' => 'Past Concert', 'event_date' => now()->subDays(2)->toDateString(), 'location' => 'Jakarta', 'status' => 'on-air', 'created_at' => now(), 'updated_at' => now()],
            ['event_name' => 'Upcoming Concert', 'event_date' => now()->addDays(2)->toDateString(), 'location' => 'Jakarta', 'status' => 'on-air', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('meet_greet_events')->insert([
            ['event_name' => 'Past Meet', 'event_date' => now()->subDays(3)->toDateString(), 'event_type' => 'meet-greet', 'created_at' => now(), 'updated_at' => now()],
            ['event_name' => 'Upcoming Meet', 'event_date' => now()->addDays(3)->toDateString(), 'event_type' => 'meet-greet', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk()->assertViewHas('period', 'all');
        $this->assertSame(['Past Show', 'Past Concert', 'Past Meet'], $response->viewData('pastEvents')->pluck('name')->all());
        $this->assertSame(['Upcoming Show', 'Upcoming Concert', 'Upcoming Meet'], $response->viewData('upcomingEvents')->pluck('name')->all());
    }

    public function test_live_streaming_uses_the_dashboard_period_and_event_display_limit(): void
    {
        foreach (range(1, 6) as $index) {
            DB::table('live_streaming')->insert([
                'platform' => 'Showroom',
                'live_date' => now()->subDays($index)->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('live_streaming')->insert([
            'platform' => 'IDN App',
            'live_date' => now()->subMonths(2)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard', [
            'period' => '7days',
            'event_display_limit' => 5,
        ]));

        $response->assertOk();
        $liveStreamingEvents = $response->viewData('liveStreamingEvents');
        $this->assertCount(5, $liveStreamingEvents);
        $this->assertTrue($liveStreamingEvents->every(fn (object $event): bool => Carbon::parse($event->live_date)->greaterThanOrEqualTo(now()->subDays(6)->startOfDay())));
    }

    public function test_dashboard_classifies_events_using_the_jakarta_calendar_date(): void
    {
        $this->travelTo(Carbon::parse('2026-09-02 18:00:00', 'UTC'));

        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026-09-02', 'setlist' => 'Yesterday Show'],
            ['show_id' => 2, 'show_date' => '2026-09-03', 'setlist' => 'Today Show'],
            ['show_id' => 3, 'show_date' => '2026-09-04', 'setlist' => 'Tomorrow Show'],
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk();
        $this->assertSame(['Yesterday Show'], $response->viewData('pastEvents')->pluck('name')->all());
        $this->assertSame(['Tomorrow Show'], $response->viewData('upcomingEvents')->pluck('name')->all());
    }

    public function test_dashboard_normalizes_slash_formatted_show_dates_for_period_filters(): void
    {
        $this->travelTo(Carbon::parse('2026-09-02 12:00:00', 'Asia/Jakarta'));

        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026/08/25', 'setlist' => 'Outside Range', 'unit_song' => null, 'is_us_center' => null, 'is_global_center' => null],
            ['show_id' => 2, 'show_date' => '2026/08/28', 'setlist' => 'Past In Range', 'unit_song' => 'Song A', 'is_us_center' => 1, 'is_global_center' => 1],
            ['show_id' => 3, 'show_date' => '2026/09/03', 'setlist' => 'Upcoming In Range', 'unit_song' => 'Song B', 'is_us_center' => null, 'is_global_center' => null],
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard', ['period' => '7days']));

        $response->assertOk();
        $this->assertSame(1, $response->viewData('stats')['total_shows']);
        $this->assertSame(1, $response->viewData('stats')['global_center']);
        $this->assertSame(1, array_sum($response->viewData('chartShowTeater')));
        $this->assertSame(['Past In Range'], $response->viewData('pastEvents')->pluck('name')->all());
        $this->assertSame([], $response->viewData('upcomingEvents')->pluck('name')->all());
    }
}
