<?php

namespace App\Support;

use App\Models\ConcertEvents;
use App\Models\LiveStreaming;
use App\Models\MeetGreetEvents;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class MonthlySchedule
{
    private const TIMEZONE = 'Asia/Jakarta';

    /**
     * Build the schedule data (events, calendar weeks, and month navigation).
     *
     * @return array{
     *     year: int,
     *     month: int,
     *     label: string,
     *     events: Collection<int, array<string, mixed>>,
     *     byDate: array<string, array<int, array<string, mixed>>>,
     *     weeks: array<int, array<int, array<string, mixed>>>,
     *     prev: array{month: int, year: int},
     *     next: array{month: int, year: int},
     *     today: string,
     *     birthday: array{month: int, day: int}|null,
     *     idolName: string
     * }
     */
    public function build(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1, 0, 0, 0, self::TIMEZONE)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $about = SettingBag::about();
        $birthday = $this->birthday($about['idol_birth_date'] ?? null);

        $events = $this->events($start, $end);

        $byDate = [];
        foreach ($events as $event) {
            $byDate[$event['date']][] = $event;
        }

        return [
            'year' => $year,
            'month' => $month,
            'label' => $start->locale('id')->isoFormat('MMMM YYYY'),
            'events' => $events,
            'byDate' => $byDate,
            'weeks' => $this->weeks($start, $byDate, $birthday),
            'prev' => $this->adjacent($start, -1),
            'next' => $this->adjacent($start, 1),
            'today' => now(self::TIMEZONE)->toDateString(),
            'birthday' => $birthday,
            'idolName' => $about['idol_name'] ?? 'Oshimen',
        ];
    }

    /**
     * @return array{month: int, day: int}|null
     */
    private function birthday(?string $birthDate): ?array
    {
        if (! $birthDate) {
            return null;
        }

        try {
            $parsed = Carbon::parse($birthDate);
        } catch (\Throwable) {
            return null;
        }

        return ['month' => (int) $parsed->month, 'day' => (int) $parsed->day];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function events(Carbon $start, Carbon $end): Collection
    {
        $from = $start->toDateString();
        $to = $end->toDateString();
        $events = collect();

        $showDate = ShowDate::sqlExpression();
        foreach (DB::table('show_teater')
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw($showDate), [$from, $to])
            ->orderBy('show_date')
            ->get(['setlist', 'unit_song', 'show_date']) as $show) {
            $events->push($this->event('Show Teater', 'blue', $show->setlist, ShowDate::normalize($show->show_date), $show->unit_song ?: null));
        }

        foreach (ConcertEvents::query()
            ->whereBetween('event_date', [$from, $to])
            ->orderBy('event_date')
            ->get() as $concert) {
            $events->push($this->event('Event', 'red', $concert->event_name, $concert->event_date?->toDateString(), $concert->location, $concert->purchase_link));
        }

        foreach (MeetGreetEvents::query()
            ->where(function ($query) use ($from, $to): void {
                $query->whereBetween('event_date', [$from, $to])
                    ->orWhereBetween('event_date_2', [$from, $to]);
            })
            ->get() as $meetGreet) {
            foreach (array_filter([$meetGreet->event_date, $meetGreet->event_date_2]) as $eventDate) {
                $date = Carbon::parse($eventDate)->toDateString();

                if ($date < $from || $date > $to) {
                    continue;
                }

                $events->push($this->event('Meet & Greet', 'orange', $meetGreet->event_name, $date, $meetGreet->location, $meetGreet->purchase_link));
            }
        }

        foreach (LiveStreaming::query()
            ->whereBetween('live_date', [$from, $to])
            ->orderBy('live_date')
            ->get() as $live) {
            $events->push($this->event('Live Streaming', 'green', $live->platform, $live->live_date?->toDateString(), $live->duration ? $live->duration.' menit' : null));
        }

        return $events
            ->sortBy(fn (array $event): string => $event['date'].'|'.$event['badge'])
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function event(string $type, string $badge, ?string $name, ?string $date, ?string $meta = null, ?string $purchaseLink = null): array
    {
        return [
            'type' => $type,
            'badge' => $badge,
            'name' => $name,
            'date' => $date,
            'meta' => $meta,
            'purchase_link' => $purchaseLink,
        ];
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $byDate
     * @param  array{month: int, day: int}|null  $birthday
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function weeks(Carbon $start, array $byDate, ?array $birthday): array
    {
        $cursor = $start->copy()->startOfWeek(Carbon::MONDAY);
        $last = $start->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $today = now(self::TIMEZONE)->toDateString();

        $weeks = [];
        $week = [];

        while ($cursor->lte($last)) {
            $key = $cursor->toDateString();
            $isBirthday = $birthday !== null
                && (int) $cursor->month === $birthday['month']
                && (int) $cursor->day === $birthday['day'];

            $week[] = [
                'date' => $key,
                'day' => (int) $cursor->format('j'),
                'in_month' => $cursor->month === $start->month,
                'is_today' => $key === $today,
                'is_birthday' => $isBirthday,
                'events' => $byDate[$key] ?? [],
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }

            $cursor->addDay();
        }

        return $weeks;
    }

    /**
     * @return array{month: int, year: int}
     */
    private function adjacent(Carbon $start, int $offset): array
    {
        $target = $start->copy()->addMonths($offset);

        return ['month' => $target->month, 'year' => $target->year];
    }
}
