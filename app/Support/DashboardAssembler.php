<?php

namespace App\Support;

use App\Models\ConcertEvents;
use App\Models\LiveStreaming;
use App\Models\MeetGreetEvents;
use App\Models\ShowTeater;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class DashboardAssembler
{
    private const ALLOWED_PERIODS = ['all', '7days', 'monthly', 'quarter', '6months', 'yearly', 'custom'];

    private const EVENT_DISPLAY_LIMITS = [5, 10, 15];

    private const STATS_CACHE_SECONDS = 300;

    private const MILESTONE_STEP = 100;

    private const BIRTHDAY_REMINDER_DAYS = 90;

    public function __construct(private EventTimeline $timeline) {}

    /**
     * @return array<string, mixed>
     */
    public function assemble(Request $request): array
    {
        $period = $request->get('period', 'all');
        if ($period === 'default' || ! in_array($period, self::ALLOWED_PERIODS, true)) {
            $period = 'all';
        }

        $showComparison = $request->boolean('comparison', false);
        $customFrom = $request->get('date_from');
        $customTo = $request->get('date_to');
        $eventDisplayLimit = (int) $request->get('event_display_limit', 5);
        if (! in_array($eventDisplayLimit, self::EVENT_DISPLAY_LIMITS, true)) {
            $eventDisplayLimit = 5;
        }

        if ($customFrom && $customTo) {
            $period = 'custom';
        }

        [$dateFrom, $dateTo, $prevFrom, $prevTo] = $this->resolvePeriod($period, $customFrom, $customTo);
        $isAllPeriod = $period === 'all';

        $about = SettingBag::about();
        $idolName = $about['idol_name'] ?? 'Oshimen';
        $idolBirthDate = $about['idol_birth_date'] ?? null;

        [$birthdayCountdown, $birthdayReminderActive] = $this->birthdayCountdown($idolBirthDate);

        $stats = $isAllPeriod ? $this->totalStats() : $this->statsFor($dateFrom, $dateTo);
        $prevStats = null;
        if ($showComparison) {
            $prevStats = Cache::remember(
                "dashboard_stats_{$prevFrom->toDateString()}_{$prevTo->toDateString()}",
                self::STATS_CACHE_SECONDS,
                fn (): array => $this->statsFor($prevFrom, $prevTo)
            );
        }

        $eventCategoryStats = $isAllPeriod
            ? $this->eventCategoryTotals()
            : $this->eventCategoryStatsFor($dateFrom, $dateTo);

        $prevEventCategoryStats = $showComparison
            ? $this->eventCategoryStatsFor($prevFrom, $prevTo)
            : null;

        $groupType = match ($period) {
            'all' => 'year',
            'yearly' => 'month',
            'quarter', '6months' => 'week',
            default => 'day',
        };

        $charts = $this->chartSeries($dateFrom, $dateTo, $groupType);

        $totalShows = Cache::remember('total_shows_count', self::STATS_CACHE_SECONDS, fn (): int => ShowTeater::query()->count());
        $nextMilestone = (int) (ceil($totalShows / self::MILESTONE_STEP) * self::MILESTONE_STEP);
        if ($nextMilestone === $totalShows) {
            $nextMilestone += self::MILESTONE_STEP;
        }
        $prevMilestone = $nextMilestone - self::MILESTONE_STEP;

        $liveStreamingEvents = collect(Cache::remember(
            "recent_live_streaming_{$dateFrom->toDateString()}_{$dateTo->toDateString()}_{$eventDisplayLimit}",
            self::STATS_CACHE_SECONDS,
            fn (): array => LiveStreaming::query()
                ->when(! $isAllPeriod, fn ($query) => $query->whereBetween('live_date', [$dateFrom->toDateString(), $dateTo->toDateString()]))
                ->orderByDesc('live_date')
                ->limit($eventDisplayLimit)
                ->get()
                ->map(fn ($item) => $item->toArray())
                ->all()
        ))->map(fn ($item) => (object) $item);

        $today = Timezone::today();
        $fromStr = $dateFrom->toDateString();
        $toStr = $dateTo->toDateString();
        $upcomingShows = $this->timeline->upcomingShowCount($today, $isAllPeriod ? null : $toStr);

        return [
            'idolName' => $idolName,
            'idolBirthDate' => $idolBirthDate,
            'birthdayCountdown' => $birthdayCountdown,
            'birthdayReminderActive' => $birthdayReminderActive,
            'stats' => $stats,
            'prevStats' => $prevStats,
            'eventCategoryStats' => $eventCategoryStats,
            'prevEventCategoryStats' => $prevEventCategoryStats,
            'period' => $period,
            'showComparison' => $showComparison,
            'customFrom' => $customFrom,
            'customTo' => $customTo,
            'eventDisplayLimit' => $eventDisplayLimit,
            'chartDates' => $charts['chartDates'],
            'chartShowTeater' => $charts['chartShowTeater'],
            'chartKonser' => $charts['chartKonser'],
            'chartMeetGreet' => $charts['chartMeetGreet'],
            'chartLiveStreaming' => $charts['chartLiveStreaming'],
            'totalShows' => $totalShows,
            'nextMilestone' => $nextMilestone,
            'prevMilestone' => $prevMilestone,
            'milestoneProgress' => $totalShows - $prevMilestone,
            'milestoneRemaining' => $nextMilestone - $totalShows,
            'liveStreamingEvents' => $liveStreamingEvents,
            'pastEvents' => $this->timeline->events('past', $isAllPeriod ? null : $fromStr, $isAllPeriod ? null : $toStr, $today, $eventDisplayLimit),
            'upcomingEvents' => $this->timeline->events('upcoming', $isAllPeriod ? null : $fromStr, $isAllPeriod ? null : $toStr, $today, $eventDisplayLimit),
            'upcomingShows' => $upcomingShows,
        ];
    }

    /**
     * @return array{0: ?int, 1: bool}
     */
    private function birthdayCountdown(?string $idolBirthDate): array
    {
        if (! $idolBirthDate) {
            return [null, false];
        }

        $nextBirthday = Carbon::parse($idolBirthDate)->setYear(Timezone::nowLocal()->year);
        if ($nextBirthday->isPast()) {
            $nextBirthday->addYear();
        }

        $countdown = (int) Timezone::nowLocal()->diffInDays($nextBirthday, false) + 1;

        return [$countdown, $countdown <= self::BIRTHDAY_REMINDER_DAYS];
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface, 2: CarbonInterface, 3: CarbonInterface}
     */
    private function resolvePeriod(string $period, ?string $customFrom = null, ?string $customTo = null): array
    {
        $now = Timezone::nowLocal();
        [$dateFrom, $dateTo] = match ($period) {
            'all' => $this->resolveAllPeriod(),
            'custom' => [
                Carbon::parse($customFrom)->startOfDay(),
                Carbon::parse($customTo)->endOfDay(),
            ],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarter' => [$now->copy()->firstOfQuarter(), $now->copy()->lastOfQuarter()],
            '6months' => [$now->copy()->subMonths(6)->startOfDay(), $now->copy()->endOfDay()],
            'yearly' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
        };
        $diffDays = $dateFrom->diffInDays($dateTo) + 1;
        $prevTo = $dateFrom->copy()->subDay();
        $prevFrom = $prevTo->copy()->subDays($diffDays - 1);

        return [$dateFrom, $dateTo, $prevFrom, $prevTo];
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function resolveAllPeriod(): array
    {
        $firstDate = collect([
            ShowTeater::query()->min('show_date'),
            ConcertEvents::query()->min('event_date'),
            MeetGreetEvents::query()->min('event_date'),
            MeetGreetEvents::query()->min('event_date_2'),
            LiveStreaming::query()->min('live_date'),
        ])->filter()->map(fn (string $date): string => ShowDate::normalize($date))->min();
        $lastDate = collect([
            ShowTeater::query()->max('show_date'),
            ConcertEvents::query()->max('event_date'),
            MeetGreetEvents::query()->max('event_date'),
            MeetGreetEvents::query()->max('event_date_2'),
            LiveStreaming::query()->max('live_date'),
        ])->filter()->map(fn (string $date): string => ShowDate::normalize($date))->max();

        return $firstDate && $lastDate
            ? [Carbon::parse($firstDate)->startOfDay(), Carbon::parse($lastDate)->endOfDay()]
            : [Timezone::nowLocal()->startOfDay(), Timezone::nowLocal()->endOfDay()];
    }

    /**
     * @return array<string, int>
     */
    private function statsFor(CarbonInterface $from, CarbonInterface $to): array
    {
        return $this->mapShowStats(
            ShowTeater::query()
                ->whereBetween(DB::raw(ShowDate::sqlExpression()), [$from->toDateString(), $to->toDateString()])
                ->selectRaw($this->showStatsSelect())
                ->first()
        );
    }

    /**
     * @return array<string, int>
     */
    private function totalStats(): array
    {
        return $this->mapShowStats(
            ShowTeater::query()->selectRaw($this->showStatsSelect())->first()
        );
    }

    private function showStatsSelect(): string
    {
        return 'COUNT(*) as total, COUNT(DISTINCT setlist) as setlists, COUNT(DISTINCT unit_song) as unit_songs, SUM(CASE WHEN is_us_center IS NOT NULL THEN 1 ELSE 0 END) as us_center, SUM(CASE WHEN is_global_center = 1 THEN 1 ELSE 0 END) as global_center';
    }

    /**
     * @return array<string, int>
     */
    private function mapShowStats(?object $shows): array
    {
        return [
            'total_shows' => (int) ($shows?->total ?? 0),
            'setlists' => (int) ($shows?->setlists ?? 0),
            'unit_songs' => (int) ($shows?->unit_songs ?? 0),
            'us_center' => (int) ($shows?->us_center ?? 0),
            'global_center' => (int) ($shows?->global_center ?? 0),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function eventCategoryTotals(): array
    {
        return $this->mapEventCategoryStats(
            ConcertEvents::query()
                ->selectRaw($this->eventCategorySelect())
                ->first()
        );
    }

    /**
     * @return array<string, int>
     */
    private function eventCategoryStatsFor(CarbonInterface $from, CarbonInterface $to): array
    {
        return $this->mapEventCategoryStats(
            ConcertEvents::query()
                ->whereBetween('event_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw($this->eventCategorySelect())
                ->first()
        );
    }

    private function eventCategorySelect(): string
    {
        $aliases = [
            'off-air' => 'off_air',
            'on-air' => 'on_air',
            'brand' => 'brand',
            'media' => 'media',
            'ofc-event' => 'ofc_event',
            'jkt48-event' => 'jkt48_event',
        ];

        $sums = [];
        foreach ($aliases as $status => $alias) {
            $sums[] = "SUM(CASE WHEN status = '{$status}' THEN 1 ELSE 0 END) as {$alias}";
        }

        return implode(', ', $sums);
    }

    /**
     * @return array<string, int>
     */
    private function mapEventCategoryStats(?object $events): array
    {
        return [
            'off_air' => (int) ($events?->off_air ?? 0),
            'on_air' => (int) ($events?->on_air ?? 0),
            'brand' => (int) ($events?->brand ?? 0),
            'media' => (int) ($events?->media ?? 0),
            // OFC Event + JKT48 Event digabung.
            'concert' => (int) ($events?->ofc_event ?? 0) + (int) ($events?->jkt48_event ?? 0),
        ];
    }

    /**
     * @return array{chartDates: array<int, string>, chartShowTeater: array<int, int>, chartKonser: array<int, int>, chartMeetGreet: array<int, int>, chartLiveStreaming: array<int, int>}
     */
    private function chartSeries(CarbonInterface $dateFrom, CarbonInterface $dateTo, string $groupType): array
    {
        $groupExpression = match ($groupType) {
            'year' => 'LEFT({col}, 4)',
            'month' => 'LEFT({col}, 7)',
            'week' => 'DATE_SUB({col}, INTERVAL WEEKDAY({col}) DAY)',
            default => '{col}',
        };

        $showDateExpression = ShowDate::sqlExpression();
        $showColExpr = str_replace('{col}', $showDateExpression, $groupExpression);
        $showActivity = ShowTeater::query()
            ->whereBetween(DB::raw($showDateExpression), [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->selectRaw("{$showColExpr} as group_key, COUNT(*) as count")
            ->groupBy('group_key')
            ->get()
            ->keyBy('group_key');

        $concertColExpr = str_replace('{col}', 'event_date', $groupExpression);
        $concertActivity = ConcertEvents::query()
            ->whereBetween('event_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->selectRaw("{$concertColExpr} as group_key, COUNT(*) as count")
            ->groupBy('group_key')
            ->get()
            ->keyBy('group_key');

        $mgActivity = collect();
        MeetGreetEvents::query()
            ->where(function ($query) use ($dateFrom, $dateTo): void {
                $query->whereBetween('event_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                    ->orWhereBetween('event_date_2', [$dateFrom->toDateString(), $dateTo->toDateString()]);
            })
            ->get(['event_date', 'event_date_2'])
            ->each(function ($row) use (&$mgActivity, $dateFrom, $dateTo, $groupType): void {
                foreach (array_filter([$row->event_date, $row->event_date_2]) as $eventDate) {
                    $date = Carbon::parse($eventDate);
                    if ($date->lt($dateFrom) || $date->gt($dateTo)) {
                        continue;
                    }

                    $key = match ($groupType) {
                        'year' => $date->format('Y'),
                        'month' => $date->format('Y-m'),
                        'week' => $date->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
                        default => $date->toDateString(),
                    };

                    $count = ($mgActivity->get($key)?->count ?? 0) + 1;
                    $mgActivity->put($key, (object) ['count' => $count]);
                }
            });

        $lsColExpr = str_replace('{col}', 'live_date', $groupExpression);
        $lsActivity = LiveStreaming::query()
            ->whereBetween('live_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->selectRaw("{$lsColExpr} as group_key, COUNT(*) as count")
            ->groupBy('group_key')
            ->get()
            ->keyBy('group_key');

        return $this->fillChartBuckets($dateFrom, $dateTo, $groupType, $showActivity, $concertActivity, $mgActivity, $lsActivity);
    }

    /**
     * @return array{chartDates: array<int, string>, chartShowTeater: array<int, int>, chartKonser: array<int, int>, chartMeetGreet: array<int, int>, chartLiveStreaming: array<int, int>}
     */
    private function fillChartBuckets(
        CarbonInterface $dateFrom,
        CarbonInterface $dateTo,
        string $groupType,
        Collection $showActivity,
        Collection $concertActivity,
        Collection $mgActivity,
        Collection $lsActivity,
    ): array {
        [$current, $keyOf, $labelOf, $advance] = match ($groupType) {
            'year' => [
                $dateFrom->copy()->startOfYear(),
                fn (CarbonInterface $date): string => $date->format('Y'),
                fn (CarbonInterface $date): string => $date->format('Y'),
                fn (CarbonInterface $date): CarbonInterface => $date->addYear(),
            ],
            'month' => [
                $dateFrom->copy()->startOfMonth(),
                fn (CarbonInterface $date): string => $date->format('Y-m'),
                fn (CarbonInterface $date): string => $date->locale('id')->isoFormat('MMMM YYYY'),
                fn (CarbonInterface $date): CarbonInterface => $date->addMonth(),
            ],
            'week' => [
                $dateFrom->copy()->startOfWeek(),
                fn (CarbonInterface $date): string => $date->toDateString(),
                fn (CarbonInterface $date): string => $date->locale('id')->isoFormat('D MMM'),
                fn (CarbonInterface $date): CarbonInterface => $date->addWeek(),
            ],
            default => [
                $dateFrom->copy(),
                fn (CarbonInterface $date): string => $date->toDateString(),
                fn (CarbonInterface $date): string => $date->locale('id')->isoFormat('ddd, D MMM'),
                fn (CarbonInterface $date): CarbonInterface => $date->addDay(),
            ],
        };

        $chartDates = [];
        $chartShowTeater = [];
        $chartKonser = [];
        $chartMeetGreet = [];
        $chartLiveStreaming = [];

        while ($current->lte($dateTo)) {
            $key = $keyOf($current);
            $chartDates[] = $labelOf($current);
            $chartShowTeater[] = $showActivity->get($key)?->count ?? 0;
            $chartKonser[] = $concertActivity->get($key)?->count ?? 0;
            $chartMeetGreet[] = $mgActivity->get($key)?->count ?? 0;
            $chartLiveStreaming[] = $lsActivity->get($key)?->count ?? 0;
            $current = $advance($current);
        }

        return compact('chartDates', 'chartShowTeater', 'chartKonser', 'chartMeetGreet', 'chartLiveStreaming');
    }
}
