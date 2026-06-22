<?php

namespace App\Http\Controllers;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'default');
        $showComparison = $request->boolean('comparison', false);
        $customFrom = $request->get('date_from');
        $customTo = $request->get('date_to');

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
        if ($period === 'default') {
            $stats = Cache::remember(
                'dashboard_stats_total',
                300,
                fn () => $this->getTotalStats()
            );
            $prevStats = null;
        } else {
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
            ->selectRaw("$concertColExpr as group_key, COUNT(*) as count")
            ->groupBy('group_key')
            ->get()
            ->keyBy('group_key');

        // --- Meet & Greet activity ---
        $mgColExpr = str_replace('{col}', 'event_date', $groupExpression);
        $mgActivity = DB::table('meet_greet_events')
            ->whereBetween('event_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
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

        // --- Live streaming last 7 days ---
        $todayStr = now()->toDateString();
        $recentLiveStreaming = collect(Cache::remember(
            "recent_live_streaming_{$todayStr}",
            300,
            fn () => DB::table('live_streaming')
                ->where('live_date', '>=', now()->subDays(7)->toDateString())
                ->orderByDesc('live_date')
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()
        ))->map(fn ($item) => (object) $item);

        // --- Past events (shows + concerts + MG) ---
        $today = now()->toDateString();

        if ($period === 'default') {
            $pastShowTeater = collect(Cache::remember("past_show_teater_default_{$today}", 300, fn () => DB::table('show_teater')
                ->where('show_date', '<', $today)
                ->where('is_the_show_has_event', '!=', '')
                ->whereNotNull('is_the_show_has_event')
                ->orderByDesc('show_date')
                ->limit(5)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);

            $pastConcerts = collect(Cache::remember("past_concerts_default_{$today}", 300, fn () => DB::table('concert_events')
                ->where('event_date', '<', $today)
                ->orderByDesc('event_date')
                ->limit(5)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);

            $pastMeetGreet = collect(Cache::remember("past_meet_greet_default_{$today}", 300, fn () => DB::table('meet_greet_events')
                ->where('event_date', '<', $today)
                ->orderByDesc('event_date')
                ->limit(5)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);

            $upcomingShowTeater = collect(Cache::remember("upcoming_show_teater_default_{$today}", 300, fn () => DB::table('show_teater')
                ->where('show_date', '>=', $today)
                ->whereNotNull('is_the_show_has_event')
                ->where('is_the_show_has_event', '!=', '')
                ->orderBy('show_date')
                ->limit(5)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);

            $upcomingConcerts = collect(Cache::remember("upcoming_concerts_default_{$today}", 300, fn () => DB::table('concert_events')
                ->where('event_date', '>=', $today)
                ->orderBy('event_date')
                ->limit(5)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);

            $upcomingMeetGreet = collect(Cache::remember("upcoming_meet_greet_default_{$today}", 300, fn () => DB::table('meet_greet_events')
                ->where('event_date', '>=', $today)
                ->orderBy('event_date')
                ->limit(5)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);
        } else {
            $fromStr = $dateFrom->toDateString();
            $toStr = $dateTo->toDateString();

            $pastShowTeater = collect(Cache::remember("past_show_teater_{$fromStr}_{$toStr}", 300, fn () => DB::table('show_teater')
                ->where('show_date', '<', $today)
                ->where('show_date', '>=', $fromStr)
                ->where('is_the_show_has_event', '!=', '')
                ->whereNotNull('is_the_show_has_event')
                ->orderByDesc('show_date')
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);

            $pastConcerts = collect(Cache::remember("past_concerts_{$fromStr}_{$toStr}", 300, fn () => DB::table('concert_events')
                ->where('event_date', '<', $today)
                ->where('event_date', '>=', $fromStr)
                ->orderByDesc('event_date')
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);

            $pastMeetGreet = collect(Cache::remember("past_meet_greet_{$fromStr}_{$toStr}", 300, fn () => DB::table('meet_greet_events')
                ->where('event_date', '<', $today)
                ->where('event_date', '>=', $fromStr)
                ->orderByDesc('event_date')
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);

            $upcomingShowTeater = collect(Cache::remember("upcoming_show_teater_{$fromStr}_{$toStr}", 300, fn () => DB::table('show_teater')
                ->where('show_date', '>=', $today)
                ->where('show_date', '<=', $toStr)
                ->whereNotNull('is_the_show_has_event')
                ->where('is_the_show_has_event', '!=', '')
                ->orderBy('show_date')
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);

            $upcomingConcerts = collect(Cache::remember("upcoming_concerts_{$fromStr}_{$toStr}", 300, fn () => DB::table('concert_events')
                ->where('event_date', '>=', $today)
                ->where('event_date', '<=', $toStr)
                ->orderBy('event_date')
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);

            $upcomingMeetGreet = collect(Cache::remember("upcoming_meet_greet_{$fromStr}_{$toStr}", 300, fn () => DB::table('meet_greet_events')
                ->where('event_date', '>=', $today)
                ->where('event_date', '<=', $toStr)
                ->orderBy('event_date')
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all()))->map(fn ($item) => (object) $item);
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
            'recentLiveStreaming',
            'pastShowTeater',
            'pastConcerts',
            'pastMeetGreet',
            'upcomingShowTeater',
            'upcomingConcerts',
            'upcomingMeetGreet',
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
}
