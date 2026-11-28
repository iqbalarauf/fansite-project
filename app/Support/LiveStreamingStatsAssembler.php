<?php

namespace App\Support;

use App\Models\LiveStreaming;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Ringkasan statistik live streaming untuk dashboard admin.
 *
 * Semua angka mengikuti filter periode yang sama dengan dashboard utama
 * (All, 7 Hari, Bulanan, Kuartal, 6 Bulan, 1 Tahun).
 */
final class LiveStreamingStatsAssembler
{
    private const ALLOWED_PERIODS = ['all', '7days', 'monthly', 'quarter', '6months', 'yearly'];

    private const CACHE_SECONDS = 300;

    private const LEADERBOARD_LIMIT = 10;

    /**
     * Platform yang dikenal oleh tabel live_streaming.
     *
     * @var array<int, string>
     */
    private const PLATFORMS = ['IDN App', 'Showroom'];

    /**
     * @return array<string, mixed>
     */
    public function assemble(Request $request): array
    {
        $period = (string) $request->get('period', 'all');

        if (! in_array($period, self::ALLOWED_PERIODS, true)) {
            $period = 'all';
        }

        [$dateFrom, $dateTo] = $this->resolveRange($period);

        $stats = Cache::remember(
            "live_streaming_stats_{$dateFrom->toDateString()}_{$dateTo->toDateString()}",
            self::CACHE_SECONDS,
            fn (): array => $this->build($dateFrom, $dateTo),
        );

        return array_merge($stats, [
            'period' => $period,
            'periodLabel' => $this->periodLabel($period),
            'periodFrom' => $dateFrom,
            'periodTo' => $dateTo,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function build(CarbonInterface $dateFrom, CarbonInterface $dateTo): array
    {
        $lives = LiveStreaming::query()
            ->whereBetween('live_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->get();

        $durations = $this->numericValues($lives->pluck('duration'));
        $giftCounts = $this->numericValues($lives->pluck('gift_count'));

        $platformStats = [];

        foreach (self::PLATFORMS as $platform) {
            $platformLives = $lives->where('platform', $platform);
            $platformDurations = $this->numericValues($platformLives->pluck('duration'));
            $platformGiftCounts = $this->numericValues($platformLives->pluck('gift_count'));
            $gifts = $this->aggregateGifts($platformLives);

            $platformStats[$platform] = [
                'name' => $platform,
                'count' => $platformLives->count(),
                'duration' => array_sum($platformDurations),
                'avg_duration' => $this->average($platformDurations),
                'gift_total' => array_sum($platformGiftCounts),
                'gift_avg' => $this->average($platformGiftCounts),
                'gift_median' => $this->median($platformGiftCounts),
                'gold_total' => (int) $platformLives->sum('total_gold'),
                'top_gift' => $gifts[0] ?? null,
                'gifts' => $gifts,
            ];
        }

        $giftTypes = $this->aggregateGifts($lives);

        return [
            'totalLives' => $lives->count(),
            'totalDuration' => array_sum($durations),
            'avgDuration' => $this->average($durations),
            'totalGifts' => array_sum($giftCounts),
            'totalGold' => (int) $lives->sum('total_gold'),
            'durationStats' => [
                'avg' => $this->average($durations),
                'median' => $this->median($durations),
                'min' => $durations === [] ? null : min($durations),
                'max' => $durations === [] ? null : max($durations),
            ],
            'giftStats' => [
                'avg' => $this->average($giftCounts),
                'median' => $this->median($giftCounts),
            ],
            'platformStats' => $platformStats,
            'leaderboard' => $this->leaderboard($lives),
            'dayStats' => $this->dayStats($lives),
            'hourStats' => $this->hourStats($lives),
            'topGift' => $giftTypes[0] ?? null,
            'giftTypes' => $giftTypes,
        ];
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function resolveRange(string $period): array
    {
        $now = Timezone::nowLocal();

        return match ($period) {
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarter' => [$now->copy()->firstOfQuarter(), $now->copy()->lastOfQuarter()],
            '6months' => [$now->copy()->subMonths(6)->startOfDay(), $now->copy()->endOfDay()],
            'yearly' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            '7days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            default => $this->resolveAllRange(),
        };
    }

    /**
     * Rentang "All" mengikuti data live yang tersedia, bukan data show/event.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function resolveAllRange(): array
    {
        $first = LiveStreaming::query()->min('live_date');
        $last = LiveStreaming::query()->max('live_date');

        if (! $first || ! $last) {
            $today = Timezone::nowLocal();

            return [$today->copy()->startOfDay(), $today->copy()->endOfDay()];
        }

        return [
            Carbon::parse((string) $first)->startOfDay(),
            Carbon::parse((string) $last)->endOfDay(),
        ];
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            '7days' => '7 Hari',
            'monthly' => 'Bulanan',
            'quarter' => 'Kuartal',
            '6months' => '6 Bulan',
            'yearly' => '1 Tahun',
            default => 'All',
        };
    }

    /**
     * @param  Collection<int, mixed>  $values
     * @return array<int, int>
     */
    private function numericValues(Collection $values): array
    {
        return $values
            ->filter(fn (mixed $value): bool => is_numeric($value))
            ->map(fn (mixed $value): int => (int) $value)
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int>  $values
     */
    private function average(array $values): ?float
    {
        return $values === [] ? null : (float) (array_sum($values) / count($values));
    }

    /**
     * @param  array<int, int>  $values
     */
    private function median(array $values): ?float
    {
        $count = count($values);

        if ($count === 0) {
            return null;
        }

        sort($values);
        $middle = intdiv($count, 2);

        return $count % 2 === 1
            ? (float) $values[$middle]
            : (float) (($values[$middle - 1] + $values[$middle]) / 2);
    }

    /**
     * Gabungkan top_senders dari semua live menjadi satu leaderboard.
     *
     * @param  Collection<int, LiveStreaming>  $lives
     * @return array<int, array{name: string, avatar: ?string, total_gold: int, lives: int, best_gold: int}>
     */
    private function leaderboard(Collection $lives): array
    {
        $aggregated = [];

        foreach ($lives as $live) {
            foreach (($live->top_senders ?? []) as $sender) {
                if (! is_array($sender)) {
                    continue;
                }

                $name = trim((string) ($sender['name'] ?? ''));

                if ($name === '') {
                    continue;
                }

                $key = mb_strtolower($name);
                $gold = (int) ($sender['total_gold'] ?? 0);

                $aggregated[$key] ??= [
                    'name' => $name,
                    'avatar' => null,
                    'total_gold' => 0,
                    'lives' => 0,
                    'best_gold' => 0,
                ];

                $aggregated[$key]['total_gold'] += $gold;
                $aggregated[$key]['lives']++;
                $aggregated[$key]['best_gold'] = max($aggregated[$key]['best_gold'], $gold);

                if ($aggregated[$key]['avatar'] === null && filled($sender['avatar'] ?? null)) {
                    $aggregated[$key]['avatar'] = (string) $sender['avatar'];
                }
            }
        }

        usort(
            $aggregated,
            fn (array $left, array $right): int => $right['total_gold'] <=> $left['total_gold'],
        );

        return array_slice($aggregated, 0, self::LEADERBOARD_LIMIT);
    }

    /**
     * Gabungkan daftar gift dari setiap live menjadi statistik per jenis gift.
     *
     * @param  Collection<int, LiveStreaming>  $lives
     * @return array<int, array{name: string, image_url: ?string, count: int, total_gold: int, sessions: int}>
     */
    private function aggregateGifts(Collection $lives): array
    {
        $aggregated = [];

        foreach ($lives as $live) {
            foreach (($live->gifts ?? []) as $gift) {
                if (! is_array($gift)) {
                    continue;
                }

                $name = trim((string) ($gift['name'] ?? ''));

                if ($name === '') {
                    continue;
                }

                $key = mb_strtolower($name);
                $count = (int) ($gift['count'] ?? 0);
                $totalGold = (int) ($gift['total_gold'] ?? 0);

                if ($totalGold === 0 && isset($gift['gold_per_unit'])) {
                    $totalGold = (int) $gift['gold_per_unit'] * $count;
                }

                $aggregated[$key] ??= [
                    'name' => $name,
                    'image_url' => null,
                    'count' => 0,
                    'total_gold' => 0,
                    'sessions' => 0,
                ];

                $aggregated[$key]['count'] += $count;
                $aggregated[$key]['total_gold'] += $totalGold;
                $aggregated[$key]['sessions']++;

                if ($aggregated[$key]['image_url'] === null && filled($gift['image_url'] ?? null)) {
                    $aggregated[$key]['image_url'] = (string) $gift['image_url'];
                }
            }
        }

        usort(
            $aggregated,
            fn (array $left, array $right): int => [$right['count'], $right['total_gold']] <=> [$left['count'], $left['total_gold']],
        );

        return array_values($aggregated);
    }

    /**
     * @param  Collection<int, LiveStreaming>  $lives
     * @return array{top: ?array{label: string, count: int}, breakdown: array<int, array{label: string, count: int}>}
     */
    private function dayStats(Collection $lives): array
    {
        $counts = array_fill(1, 7, 0);

        foreach ($lives as $live) {
            if ($live->live_date === null) {
                continue;
            }

            $counts[(int) $live->live_date->dayOfWeekIso]++;
        }

        $breakdown = [];

        for ($day = 1; $day <= 7; $day++) {
            $breakdown[] = [
                'label' => Carbon::now()
                    ->startOfWeek(Carbon::MONDAY)
                    ->addDays($day - 1)
                    ->locale('id')
                    ->isoFormat('dddd'),
                'count' => $counts[$day],
            ];
        }

        $top = null;

        foreach ($breakdown as $row) {
            if ($row['count'] > 0 && ($top === null || $row['count'] > $top['count'])) {
                $top = $row;
            }
        }

        return ['top' => $top, 'breakdown' => $breakdown];
    }

    /**
     * @param  Collection<int, LiveStreaming>  $lives
     * @return array{top: ?array{label: string, count: int}, ranked: array<int, array{label: string, count: int}>}
     */
    private function hourStats(Collection $lives): array
    {
        $counts = array_fill(0, 24, 0);

        foreach ($lives as $live) {
            if ($live->start_time === null) {
                continue;
            }

            $counts[(int) $live->start_time->format('G')]++;
        }

        $ranked = [];

        for ($hour = 0; $hour < 24; $hour++) {
            if ($counts[$hour] === 0) {
                continue;
            }

            $ranked[] = [
                'label' => sprintf('%02d:00', $hour),
                'count' => $counts[$hour],
            ];
        }

        usort($ranked, fn (array $left, array $right): int => $right['count'] <=> $left['count']);

        return [
            'top' => $ranked[0] ?? null,
            'ranked' => array_slice($ranked, 0, 5),
        ];
    }
}
