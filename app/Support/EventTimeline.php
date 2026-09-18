<?php

namespace App\Support;

use App\Models\ConcertEvents;
use App\Models\MeetGreetEvents;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class EventTimeline
{
    /**
     * @return Collection<int, array{type: string, name: string|null, date: string, badge_color: string, purchase_link: ?string}>
     */
    public function events(string $direction, ?string $from, ?string $to, string $today, int $limit): Collection
    {
        $past = $direction === 'past';
        $showDateExpression = ShowDate::sqlExpression();

        $showQuery = DB::table('show_teater')
            ->whereNull('deleted_at')
            ->when($from && $to, fn ($query) => $query->whereBetween(DB::raw($showDateExpression), [$from, $to]))
            ->when($past, function ($query) use ($today, $showDateExpression): void {
                $query->whereRaw("{$showDateExpression} < ?", [$today]);
            }, function ($query) use ($today, $showDateExpression): void {
                $query->whereRaw("{$showDateExpression} >= ?", [$today]);
            })
            ->orderBy('show_date', $past ? 'desc' : 'asc');

        $concertQuery = ConcertEvents::query()
            ->when($from && $to, fn ($query) => $query->whereBetween('event_date', [$from, $to]))
            ->when($past, function ($query) use ($today): void {
                $query->whereDate('event_date', '<', $today);
            }, function ($query) use ($today): void {
                $query->whereDate('event_date', '>=', $today);
            })
            ->orderBy('event_date', $past ? 'desc' : 'asc');

        $events = collect();

        foreach ($showQuery->get() as $show) {
            $events->push([
                'type' => 'Show Teater',
                'name' => $show->setlist,
                'date' => ShowDate::normalize($show->show_date),
                'badge_color' => 'blue',
            ]);
        }

        foreach ($concertQuery->get() as $concert) {
            $events->push([
                'type' => 'Event',
                'name' => $concert->event_name,
                'date' => ShowDate::normalize((string) $concert->event_date),
                'badge_color' => 'red',
                'purchase_link' => $concert->purchase_link,
            ]);
        }

        foreach (MeetGreetEvents::query()->get() as $meetGreet) {
            foreach (array_filter([$meetGreet->event_date, $meetGreet->event_date_2]) as $eventDate) {
                $normalizedDate = ShowDate::normalize((string) $eventDate);
                $withinRange = ! ($from && $to) || ($normalizedDate >= $from && $normalizedDate <= $to);
                $matchesDirection = $past ? $normalizedDate < $today : $normalizedDate >= $today;

                if ($withinRange && $matchesDirection) {
                    $events->push([
                        'type' => 'Meet & Greet',
                        'name' => $meetGreet->event_name,
                        'date' => $normalizedDate,
                        'badge_color' => 'orange',
                        'purchase_link' => $meetGreet->purchase_link,
                    ]);
                }
            }
        }

        $events = $past ? $events->sortByDesc('date')->values() : $events->sortBy('date')->values();

        return $events->take($limit)->values();
    }

    public function upcomingShowCount(string $today, ?string $until = null): int
    {
        $expression = ShowDate::sqlExpression();

        return DB::table('show_teater')
            ->whereNull('deleted_at')
            ->whereRaw("{$expression} > ?", [$today])
            ->when($until !== null, fn ($query) => $query->whereRaw("{$expression} <= ?", [$until]))
            ->count();
    }
}
