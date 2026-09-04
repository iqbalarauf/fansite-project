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
        $period = $request->get('period', 'all');
        if ($period === 'default') {
            $period = 'all';
        }

        $allowedPeriods = ['all', '7days', 'monthly', 'quarter', '6months', 'yearly', 'custom'];
        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'all';
        }

        $showComparison = $request->boolean('comparison', false);
        $customFrom = $request->get('date_from');
        $customTo = $request->get('date_to');
        $eventDisplayLimit = (int) $request->get('event_display_limit', 5);
        if (! in_array($eventDisplayLimit, [5, 10, 15], true)) {
            $eventDisplayLimit = 5;
        }

        // Auto-detect custom period when dates are provided
        if ($customFrom && $customTo) {
            $period = 'custom';
        }

        [$dateFrom, $dateTo, $prevFrom, $prevTo] = $this->resolvePeriod($period, $customFrom, $customTo);
        $isAllPeriod = $period === 'all';

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

        // --- Statistics follow the selected dashboard period ---
        $stats = $isAllPeriod ? $this->getTotalStats() : $this->getStats($dateFrom, $dateTo);

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
            'all' => 'year',
            'yearly' => 'month',
            'quarter', '6months' => 'week',
            default => 'day',
        };

        $groupExpression = match ($groupType) {
            'year' => 'LEFT({col}, 4)',
            'month' => 'LEFT({col}, 7)',
            'week' => 'DATE_SUB({col}, INTERVAL WEEKDAY({col}) DAY)',
            default => '{col}',
        };

        // --- Show Teater activity per day/week/month in period (for chart) ---
        $showDateExpression = "REPLACE(show_date, '/', '-')";
        $showColExpr = str_replace('{col}', $showDateExpression, $groupExpression);
        $showActivity = DB::table('show_teater')
            ->whereBetween(DB::raw($showDateExpression), [$dateFrom->toDateString(), $dateTo->toDateString()])
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
        // Video Call events may carry a second date (event_date_2), so each
        // filled date column is counted as its own occurrence on the chart.
        $mgActivity = collect();
        DB::table('meet_greet_events')
            ->whereNull('deleted_at')
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

        if ($groupType === 'year') {
            $current = $dateFrom->copy()->startOfYear();
            while ($current->lte($dateTo)) {
                $key = $current->format('Y');
                $chartDates[] = $key;
                $chartShowTeater[] = $showActivity->get($key)?->count ?? 0;
                $chartKonser[] = $concertActivity->get($key)?->count ?? 0;
                $chartMeetGreet[] = $mgActivity->get($key)?->count ?? 0;
                $chartLiveStreaming[] = $lsActivity->get($key)?->count ?? 0;
                $current = $current->addYear();
            }
        } elseif ($groupType === 'month') {
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
            "recent_live_streaming_{$dateFrom->toDateString()}_{$dateTo->toDateString()}_{$eventDisplayLimit}",
            300,
            fn () => DB::table('live_streaming')
                ->when(! $isAllPeriod, fn ($query) => $query->whereBetween('live_date', [$dateFrom->toDateString(), $dateTo->toDateString()]))
                ->orderByDesc('live_date')
                ->limit($eventDisplayLimit)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()
        ))->map(fn ($item) => (object) $item);

        // --- Past / upcoming events ---
        $today = now('Asia/Jakarta')->toDateString();
        $fromStr = $dateFrom->toDateString();
        $toStr = $dateTo->toDateString();

        $pastEvents = $this->buildTimelineEvents('past', $isAllPeriod ? null : $fromStr, $isAllPeriod ? null : $toStr, $today, $eventDisplayLimit);
        $upcomingEvents = $this->buildTimelineEvents('upcoming', $isAllPeriod ? null : $fromStr, $isAllPeriod ? null : $toStr, $today, $eventDisplayLimit);

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
            DB::table('show_teater')->min('show_date'),
            DB::table('concert_events')->whereNull('deleted_at')->min('event_date'),
            DB::table('meet_greet_events')->whereNull('deleted_at')->min('event_date'),
            DB::table('meet_greet_events')->whereNull('deleted_at')->min('event_date_2'),
            DB::table('live_streaming')->min('live_date'),
        ])->filter()->min();
        $lastDate = collect([
            DB::table('show_teater')->max('show_date'),
            DB::table('concert_events')->whereNull('deleted_at')->max('event_date'),
            DB::table('meet_greet_events')->whereNull('deleted_at')->max('event_date'),
            DB::table('meet_greet_events')->whereNull('deleted_at')->max('event_date_2'),
            DB::table('live_streaming')->max('live_date'),
        ])->filter()->max();

        return $firstDate && $lastDate
            ? [Carbon::parse($firstDate)->startOfDay(), Carbon::parse($lastDate)->endOfDay()]
            : [now()->startOfDay(), now()->endOfDay()];
    }

    /**
     * @return array<string, int>
     */
    private function getStats(CarbonInterface $from, CarbonInterface $to): array
    {
        $shows = DB::table('show_teater')
            ->whereBetween(DB::raw("REPLACE(show_date, '/', '-')"), [$from->toDateString(), $to->toDateString()])
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
    private function buildTimelineEvents(string $direction, ?string $from, ?string $to, string $today, int $limit): Collection
    {
        $past = $direction === 'past';
        $showDateExpression = "REPLACE(show_date, '/', '-')";

        $showQuery = DB::table('show_teater')
            ->when($from && $to, fn ($query) => $query->whereBetween(DB::raw($showDateExpression), [$from, $to]))
            ->when($past, function ($query) use ($today): void {
                $query->whereRaw("REPLACE(show_date, '/', '-') < ?", [$today]);
            }, function ($query) use ($today): void {
                $query->whereRaw("REPLACE(show_date, '/', '-') > ?", [$today]);
            })
            ->orderBy('show_date', $past ? 'desc' : 'asc');

        $concertQuery = DB::table('concert_events')
            ->whereNull('deleted_at')
            ->when($from && $to, fn ($query) => $query->whereBetween('event_date', [$from, $to]))
            ->when($past, function ($query) use ($today): void {
                $query->whereDate('event_date', '<', $today);
            }, function ($query) use ($today): void {
                $query->whereDate('event_date', '>', $today);
            })
            ->orderBy('event_date', $past ? 'desc' : 'asc');

        // Meet & Greet (Video Call type) can have a second date, so each date is
        // evaluated individually and may produce its own timeline entry.
        $meetGreetQuery = DB::table('meet_greet_events')->whereNull('deleted_at');

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
                'type' => 'Event',
                'name' => $concert->event_name,
                'date' => $concert->event_date,
                'badge_color' => 'red',
            ]);
        }

        foreach ($meetGreetQuery->get() as $meetGreet) {
            foreach (array_filter([$meetGreet->event_date, $meetGreet->event_date_2]) as $eventDate) {
                $withinRange = ! ($from && $to) || ($eventDate >= $from && $eventDate <= $to);
                $matchesDirection = $past ? $eventDate < $today : $eventDate > $today;

                if ($withinRange && $matchesDirection) {
                    $events->push([
                        'type' => 'Meet & Greet',
                        'name' => $meetGreet->event_name,
                        'date' => $eventDate,
                        'badge_color' => 'orange',
                    ]);
                }
            }
        }

        $events = $past ? $events->sortByDesc('date')->values() : $events->sortBy('date')->values();

        return $events->take($limit)->values();
    }
}
