<?php

namespace Tests\Feature;

use App\Models\LiveStreaming;
use App\Models\User;
use App\Support\Timezone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LiveStreamingDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Mulai dari data yang deterministik; rollback transaksi mengembalikan isi tabel.
        LiveStreaming::query()->delete();
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('live-streaming.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_super_admin_can_open_the_live_streaming_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('live-streaming.dashboard'))
            ->assertOk()
            ->assertSee('Statistik Live Streaming')
            ->assertSee('Leaderboard Top Gifters')
            ->assertSee('Hari Paling Sering Live')
            ->assertSee('Jam Paling Sering Live')
            ->assertSee('Durasi Live');
    }

    public function test_content_creator_cannot_open_the_dashboard(): void
    {
        $this->actingAs(User::factory()->contentCreator()->create())
            ->get(route('live-streaming.dashboard'))
            ->assertForbidden();
    }

    public function test_view_only_and_bank_data_admin_can_open_the_dashboard(): void
    {
        $this->actingAs(User::factory()->viewOnly()->create())
            ->get(route('live-streaming.dashboard'))
            ->assertOk();

        $this->actingAs(User::factory()->bankDataAdmin()->create())
            ->get(route('live-streaming.dashboard'))
            ->assertOk();
    }

    public function test_dashboard_passes_required_view_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('live-streaming.dashboard'))
            ->assertOk()
            ->assertViewHasAll([
                'period',
                'periodLabel',
                'periodFrom',
                'periodTo',
                'totalLives',
                'totalDuration',
                'avgDuration',
                'totalGifts',
                'totalGold',
                'durationStats',
                'giftStats',
                'platformStats',
                'leaderboard',
                'dayStats',
                'hourStats',
                'topGift',
                'giftTypes',
            ]);
    }

    public function test_supported_periods_are_accepted_and_invalid_falls_back_to_all(): void
    {
        $user = User::factory()->create();

        foreach (['all', '7days', 'monthly', 'quarter', '6months', 'yearly'] as $period) {
            $this->actingAs($user)
                ->get(route('live-streaming.dashboard', ['period' => $period]))
                ->assertOk()
                ->assertViewHas('period', $period);
        }

        $this->actingAs($user)
            ->get(route('live-streaming.dashboard', ['period' => 'bogus']))
            ->assertOk()
            ->assertViewHas('period', 'all');
    }

    public function test_it_aggregates_totals_and_platform_stats(): void
    {
        $this->makeLive([
            'platform' => 'IDN App',
            'live_date' => '2026-11-02',
            'duration' => 60,
            'gift_count' => 10,
            'total_gold' => 100,
        ]);
        $this->makeLive([
            'platform' => 'IDN App',
            'live_date' => '2026-11-03',
            'duration' => 90,
            'gift_count' => 20,
            'total_gold' => 300,
        ]);
        $this->makeLive([
            'platform' => 'Showroom',
            'live_date' => '2026-11-04',
            'duration' => 30,
            'gift_count' => 6,
            'total_gold' => 60,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('live-streaming.dashboard', ['period' => 'all']))
            ->assertOk()
            ->assertViewHas('totalLives', 3)
            ->assertViewHas('totalDuration', 180)
            ->assertViewHas('avgDuration', 60.0)
            ->assertViewHas('totalGifts', 36)
            ->assertViewHas('totalGold', 460)
            ->assertViewHas('durationStats', fn (array $stats): bool => $stats['min'] === 30
                && $stats['max'] === 90
                && $stats['median'] === 60.0)
            ->assertViewHas('platformStats', fn (array $stats): bool => $stats['IDN App']['count'] === 2
                && $stats['IDN App']['duration'] === 150
                && $stats['IDN App']['avg_duration'] === 75.0
                && $stats['IDN App']['gift_total'] === 30
                && $stats['IDN App']['gift_median'] === 15.0
                && $stats['Showroom']['count'] === 1
                && $stats['Showroom']['duration'] === 30
                && $stats['Showroom']['gift_median'] === 6.0);
    }

    public function test_leaderboard_aggregates_top_gifters_across_lives(): void
    {
        $this->makeLive([
            'live_date' => '2026-11-02',
            'top_senders' => [
                ['name' => 'Alice', 'avatar' => 'https://cdn.test/alice.png', 'total_gold' => 100],
                ['name' => 'Bob', 'total_gold' => 50],
            ],
        ]);
        $this->makeLive([
            'live_date' => '2026-11-03',
            'top_senders' => [
                ['name' => 'alice', 'total_gold' => 75],
                ['name' => 'Bob', 'total_gold' => 200],
            ],
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('live-streaming.dashboard'))
            ->assertOk()
            ->assertViewHas('leaderboard', fn (array $leaders): bool => count($leaders) === 2
                && $leaders[0]['name'] === 'Bob'
                && $leaders[0]['total_gold'] === 250
                && $leaders[0]['lives'] === 2
                && $leaders[1]['name'] === 'Alice'
                && $leaders[1]['total_gold'] === 175
                && $leaders[1]['avatar'] === 'https://cdn.test/alice.png');
    }

    public function test_it_finds_the_most_frequent_day_and_start_hour(): void
    {
        $firstDay = Carbon::parse('2026-11-10');

        $this->makeLive([
            'live_date' => $firstDay->toDateString(),
            'start_time' => $firstDay->copy()->setTime(20, 0),
        ]);
        $this->makeLive([
            'live_date' => $firstDay->toDateString(),
            'start_time' => $firstDay->copy()->setTime(20, 30),
        ]);
        $this->makeLive([
            'live_date' => $firstDay->copy()->addDay()->toDateString(),
            'start_time' => $firstDay->copy()->addDay()->setTime(21, 0),
        ]);

        $expectedDay = Carbon::parse($firstDay)->locale('id')->isoFormat('dddd');

        $this->actingAs(User::factory()->create())
            ->get(route('live-streaming.dashboard'))
            ->assertOk()
            ->assertViewHas('dayStats', fn (array $stats): bool => $stats['top']['label'] === $expectedDay
                && $stats['top']['count'] === 2)
            ->assertViewHas('hourStats', fn (array $stats): bool => $stats['top']['label'] === '20:00'
                && $stats['top']['count'] === 2);
    }

    public function test_it_aggregates_gift_types_per_platform(): void
    {
        $this->makeLive([
            'platform' => 'IDN App',
            'live_date' => '2026-11-02',
            'gifts' => [
                ['name' => 'Angklung', 'image_url' => null, 'gold_per_unit' => 29, 'count' => 5, 'total_gold' => 145],
                ['name' => 'Rose', 'gold_per_unit' => 1, 'count' => 2, 'total_gold' => 2],
            ],
        ]);
        $this->makeLive([
            'platform' => 'IDN App',
            'live_date' => '2026-11-03',
            'gifts' => [
                ['name' => 'Angklung', 'gold_per_unit' => 29, 'count' => 10, 'total_gold' => 290],
            ],
        ]);
        $this->makeLive([
            'platform' => 'Showroom',
            'live_date' => '2026-11-04',
            'gifts' => [
                ['name' => 'Rose', 'gold_per_unit' => 1, 'count' => 3, 'total_gold' => 3],
            ],
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('live-streaming.dashboard'))
            ->assertOk()
            ->assertSee('Angklung')
            ->assertSee('Rose')
            ->assertSee('max-h-[620px]', false)
            ->assertSee('overflow-y-auto', false)
            ->assertViewHas('topGift', fn (?array $gift): bool => $gift !== null
                && $gift['name'] === 'Angklung'
                && $gift['count'] === 15)
            ->assertViewHas('platformStats', fn (array $stats): bool => $stats['IDN App']['top_gift']['name'] === 'Angklung'
                && $stats['IDN App']['gifts'][0]['count'] === 15
                && $stats['Showroom']['top_gift']['name'] === 'Rose');
    }

    public function test_seven_day_filter_excludes_older_lives(): void
    {
        $today = Timezone::nowLocal();

        $this->makeLive(['live_date' => $today->toDateString()]);
        $this->makeLive(['live_date' => $today->copy()->subDays(10)->toDateString()]);

        $this->actingAs(User::factory()->create())
            ->get(route('live-streaming.dashboard', ['period' => '7days']))
            ->assertOk()
            ->assertViewHas('totalLives', 1);
    }

    public function test_empty_state_renders_without_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('live-streaming.dashboard'))
            ->assertOk()
            ->assertViewHas('totalLives', 0)
            ->assertSee('Belum ada data top gifters')
            ->assertSee('Belum ada data live');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeLive(array $attributes = []): LiveStreaming
    {
        return LiveStreaming::query()->create(array_merge([
            'platform' => 'IDN App',
            'live_date' => '2026-11-20',
            'start_time' => '2026-11-20 20:00:00',
            'duration' => 60,
            'gift_count' => 0,
            'total_gold' => 0,
        ], $attributes));
    }
}
