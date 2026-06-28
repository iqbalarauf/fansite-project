<?php

namespace App\Http\Controllers;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '7days');
        if ($period === 'default') {
            $period = '7days';
        }

        $allowedPeriods = ['7days', 'monthly', 'quarter', '6months', 'yearly', 'custom'];
        if (! in_array($period, $allowedPeriods, true)) {
            $period = '7days';
        }

        $showComparison = $request->boolean('comparison', false);
        $customFrom = $request->get('date_from');
        $customTo = $request->get('date_to');
        $eventDisplayMode = $request->get('event_display_mode', 'period');
        $eventDisplayLimit = (int) $request->get('event_display_limit', 5);
        $eventLimit = (int) $request->integer('event_limit', 5);
        $eventLimit = in_array($eventLimit, [5, 10, 15], true) ? $eventLimit : 5;
        $eventsFollowPeriod = $request->has('events_follow_period')
            ? $request->boolean('events_follow_period')
            : true;

        if (! in_array($eventDisplayMode, ['period', 'count'], true)) {
            $eventDisplayMode = 'period';
        }

        if (! in_array($eventDisplayLimit, [5, 10, 15], true)) {
            $eventDisplayLimit = 5;
        }

        // Auto-detect custom period when dates are provided
        if ($customFrom && $customTo) {
            $period = 'custom';
        }

        [$dateFrom, $dateTo, $prevFrom, $prevTo] = $this->resolvePeriod($period, $customFrom, $customTo);

        // --- About / Idol settings ---
        $about = Cache::remember('about_settings', 3600, fn () => DB::table('about_settings')->pluck('value', 'key')->all());
        $idolName = $about['idol_name'] ?? 'Oshimen';
        $idolBirthDate = $about['idol_birth_date'] ?? null;

        // --- Birthday countdown ---
        $birthdayCountdown = null;
        $birthdayReminderActive = false;
        if ($idolBirthDate) {
            $bday = Carbon::parse($idolBirthDate);
            $nextBirthday = $bday->copy()->setYear(now()->year);
            if ($nextBirthday->isPast()) {
                $nextBirthday->addYear();
            }
            $birthdayCountdown = (int) now()->diffInDays($nextBirthday, false) + 1;
            $birthdayReminderActive = $birthdayCountdown <= 90;
        }

        // --- Current period stats ---
        $stats = Cache::remember(
            "dashboard_stats_{$dateFrom->toDateString()}_{$dateTo->toDateString()}",
            300,
            fn () => $this->getStats($dateFrom, $dateTo)
        );

        // --- Previous period stats (for comparison) ---
        $prevStats = null;
        if ($showComparison) {
            $prevStats = Cache::remember(
                "dashboard_stats_{$prevFrom->toDateString()}_{$prevTo->toDateString()}",
                300,
                fn () => $this->getStats($prevFrom, $prevTo)
            );
        }

        // Determine chart aggregation type based on period
        $groupType = match ($period) {
            'yearly' => 'month',
            'quarter', '6months' => 'week',
            default => 'day',
        };

        $groupExpression = match ($groupType) {
            'month' => 'LEFT({col}, 7)',
            'week' => 'DATE_SUB({col}, INTERVAL WEEKDAY({col}) DAY)',
            default => '{col}',
        };

        // --- Show Teater activity per day/week/month in period (for chart) ---
        $showColExpr = str_replace('{col}', 'show_date', $groupExpression);
        $showActivity = DB::table('show_teater')
            ->whereBetween('show_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->selectRaw("$showColExpr as group_key, COUNT(*) as count")
            ->groupBy('group_key')
            ->get()
            ->keyBy('group_key');

        // --- Concert activity ---
        $concertColExpr = str_replace('{col}', 'event_date', $groupExpression);
        $concertActivity = DB::table('concert_events')
            ->whereBetween('event_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->whereNull('deleted_at')
            ->selectRaw("$concertColExpr as group_key, COUNT(*) as count")
            ->groupBy('group_key')
            ->get()
            ->keyBy('group_key');

        // --- Meet & Greet activity ---
        $mgColExpr = str_replace('{col}', 'event_date', $groupExpression);
        $mgActivity = DB::table('meet_greet_events')
            ->whereBetween('event_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->whereNull('deleted_at')
            ->selectRaw("$mgColExpr as group_key, COUNT(*) as count")
            ->groupBy('group_key')
            ->get()
            ->keyBy('group_key');

        // --- Live streaming activity ---
        $lsColExpr = str_replace('{col}', 'live_date', $groupExpression);
        $lsActivity = DB::table('live_streaming')
            ->whereBetween('live_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->selectRaw("$lsColExpr as group_key, COUNT(*) as count")
            ->groupBy('group_key')
            ->get()
            ->keyBy('group_key');

        // Build date labels for chart
        $chartDates = [];
        $chartShowTeater = [];
        $chartKonser = [];
        $chartMeetGreet = [];
        $chartLiveStreaming = [];

        if ($groupType === 'month') {
            $current = $dateFrom->copy()->startOfMonth();
            while ($current->lte($dateTo)) {
                $key = $current->format('Y-m');
                $chartDates[] = $current->locale('id')->isoFormat('MMMM YYYY');
                $chartShowTeater[] = $showActivity->get($key)?->count ?? 0;
                $chartKonser[] = $concertActivity->get($key)?->count ?? 0;
                $chartMeetGreet[] = $mgActivity->get($key)?->count ?? 0;
                $chartLiveStreaming[] = $lsActivity->get($key)?->count ?? 0;
                $current = $current->addMonth();
            }
        } elseif ($groupType === 'week') {
            $current = $dateFrom->copy()->startOfWeek();
            while ($current->lte($dateTo)) {
                $key = $current->toDateString();
                $chartDates[] = $current->locale('id')->isoFormat('D MMM');
                $chartShowTeater[] = $showActivity->get($key)?->count ?? 0;
                $chartKonser[] = $concertActivity->get($key)?->count ?? 0;
                $chartMeetGreet[] = $mgActivity->get($key)?->count ?? 0;
                $chartLiveStreaming[] = $lsActivity->get($key)?->count ?? 0;
                $current = $current->addWeek();
            }
        } else {
            $current = $dateFrom->copy();
            while ($current->lte($dateTo)) {
                $key = $current->toDateString();
                $chartDates[] = $current->locale('id')->isoFormat('ddd, D MMM');
                $chartShowTeater[] = $showActivity->get($key)?->count ?? 0;
                $chartKonser[] = $concertActivity->get($key)?->count ?? 0;
                $chartMeetGreet[] = $mgActivity->get($key)?->count ?? 0;
                $chartLiveStreaming[] = $lsActivity->get($key)?->count ?? 0;
                $current = $current->addDay();
            }
        }

        // --- Milestone show ---
        $totalShows = Cache::remember('total_shows_count', 300, fn () => DB::table('show_teater')->count());
        $nextMilestone = (int) (ceil($totalShows / 100) * 100);
        if ($nextMilestone === $totalShows) {
            $nextMilestone += 100;
        }
        $prevMilestone = $nextMilestone - 100;
        $milestoneProgress = $totalShows - $prevMilestone;
        $milestoneRemaining = $nextMilestone - $totalShows;

        // --- Live streaming follows the selected dashboard period ---
        $liveStreamingEvents = collect(Cache::remember(
            "recent_live_streaming_{$dateFrom->toDateString()}_{$dateTo->toDateString()}",
            300,
            fn () => DB::table('live_streaming')
                ->whereBetween('live_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->orderByDesc('live_date')
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()
        ))->map(fn ($item) => (object) $item);

        // --- Past / upcoming events ---
        $today = now()->toDateString();
        $fromStr = $dateFrom->toDateString();
        $toStr = $dateTo->toDateString();

        if ($eventsFollowPeriod) {
            $pastEvents = $this->buildTimelineEvents('past', $fromStr, $toStr, $today, $eventDisplayMode, $eventDisplayLimit);
            $upcomingEvents = $this->buildTimelineEvents('upcoming', $fromStr, $toStr, $today, $eventDisplayMode, $eventDisplayLimit);
        } else {
            $pastEvents = $this->buildTimelineEvents('past', null, null, $today, $eventDisplayMode, $eventDisplayLimit);
            $upcomingEvents = $this->buildTimelineEvents('upcoming', null, null, $today, $eventDisplayMode, $eventDisplayLimit);
        }

        return view('dashboard', compact(
            'idolName',
            'idolBirthDate',
            'birthdayCountdown',
            'birthdayReminderActive',
            'stats',
            'prevStats',
            'period',
            'showComparison',
            'customFrom',
            'customTo',
            'eventDisplayMode',
            'eventDisplayLimit',
            'chartDates',
            'chartShowTeater',
            'chartKonser',
            'chartMeetGreet',
            'chartLiveStreaming',
            'totalShows',
            'nextMilestone',
            'prevMilestone',
            'milestoneProgress',
            'milestoneRemaining',
            'liveStreamingEvents',
            'pastEvents',
            'upcomingEvents',
        ));
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface, 2: CarbonInterface, 3: CarbonInterface}
     */
    private function resolvePeriod(string $period, ?string $customFrom = null, ?string $customTo = null): array
    {
        $now = now();
        [$dateFrom, $dateTo] = match ($period) {
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
     * @return array<string, int>
     */
    private function getStats(CarbonInterface $from, CarbonInterface $to): array
    {
        $shows = DB::table('show_teater')
            ->whereBetween('show_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('COUNT(*) as total, COUNT(DISTINCT setlist) as setlists, COUNT(DISTINCT unit_song) as unit_songs, SUM(CASE WHEN is_us_center IS NOT NULL THEN 1 ELSE 0 END) as us_center, SUM(CASE WHEN is_global_center = 1 THEN 1 ELSE 0 END) as global_center')
            ->first();

        return [
            'total_shows' => (int) ($shows->total ?? 0),
            'setlists' => (int) ($shows->setlists ?? 0),
            'unit_songs' => (int) ($shows->unit_songs ?? 0),
            'us_center' => (int) ($shows->us_center ?? 0),
            'global_center' => (int) ($shows->global_center ?? 0),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function getTotalStats(): array
    {
        $shows = DB::table('show_teater')
            ->selectRaw('COUNT(*) as total, COUNT(DISTINCT setlist) as setlists, COUNT(DISTINCT unit_song) as unit_songs, SUM(CASE WHEN is_us_center IS NOT NULL THEN 1 ELSE 0 END) as us_center, SUM(CASE WHEN is_global_center = 1 THEN 1 ELSE 0 END) as global_center')
            ->first();

        return [
            'total_shows' => (int) ($shows->total ?? 0),
            'setlists' => (int) ($shows->setlists ?? 0),
            'unit_songs' => (int) ($shows->unit_songs ?? 0),
            'us_center' => (int) ($shows->us_center ?? 0),
            'global_center' => (int) ($shows->global_center ?? 0),
        ];
    }

    /**
     * @return Collection<int, array{type: string, name: string|null, date: string, badge_color: string}>
     */
    private function buildTimelineEvents(string $direction, ?string $from, ?string $to, string $today, string $mode, int $limit): Collection
    {
        $past = $direction === 'past';

        $showQuery = DB::table('show_teater')
            ->whereNotNull('is_the_show_has_event')
            ->where('is_the_show_has_event', '!=', '')
            ->when($mode === 'period' && $from && $to, function ($query) use ($from, $to, $past, $today): void {
                $query->whereBetween('show_date', [$from, $to]);

                if ($past) {
                    $query->where('show_date', '<', $today);
                } else {
                    $query->where('show_date', '>=', $today);
                }
            })
            ->when($mode === 'count', function ($query) use ($past, $today): void {
                if ($past) {
                    $query->where('show_date', '<', $today);
                } else {
                    $query->where('show_date', '>=', $today);
                }
            })
            ->orderBy($past ? 'show_date' : 'show_date', $past ? 'desc' : 'asc');

        $concertQuery = DB::table('concert_events')
            ->whereNull('deleted_at')
            ->when($mode === 'period' && $from && $to, function ($query) use ($from, $to, $past, $today): void {
                $query->whereBetween('event_date', [$from, $to]);

                if ($past) {
                    $query->where('event_date', '<', $today);
                } else {
                    $query->where('event_date', '>=', $today);
                }
            })
            ->when($mode === 'count', function ($query) use ($past, $today): void {
                if ($past) {
                    $query->where('event_date', '<', $today);
                } else {
                    $query->where('event_date', '>=', $today);
                }
            })
            ->orderBy('event_date', $past ? 'desc' : 'asc');

        $meetGreetQuery = DB::table('meet_greet_events')
            ->whereNull('deleted_at')
            ->when($mode === 'period' && $from && $to, function ($query) use ($from, $to, $past, $today): void {
                $query->whereBetween('event_date', [$from, $to]);

                if ($past) {
                    $query->where('event_date', '<', $today);
                } else {
                    $query->where('event_date', '>=', $today);
                }
            })
            ->when($mode === 'count', function ($query) use ($past, $today): void {
                if ($past) {
                    $query->where('event_date', '<', $today);
                } else {
                    $query->where('event_date', '>=', $today);
                }
            })
            ->orderBy('event_date', $past ? 'desc' : 'asc');

        $events = collect();

        foreach ($showQuery->get() as $show) {
            $events->push([
                'type' => 'Show Teater',
                'name' => $show->setlist,
                'date' => $show->show_date,
                'badge_color' => 'blue',
            ]);
        }

        foreach ($concertQuery->get() as $concert) {
            $events->push([
                'type' => 'Konser',
                'name' => $concert->event_name,
                'date' => $concert->event_date,
                'badge_color' => 'red',
            ]);
        }

        foreach ($meetGreetQuery->get() as $meetGreet) {
            $events->push([
                'type' => 'Meet & Greet',
                'name' => $meetGreet->event_name,
                'date' => $meetGreet->event_date,
                'badge_color' => 'orange',
            ]);
        }

        $events = $past ? $events->sortByDesc('date')->values() : $events->sortBy('date')->values();

        if ($mode === 'count') {
            $events = $events->take($limit)->values();
        }

        return $events;
    }
}
