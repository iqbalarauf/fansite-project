<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeetGreetEventRequest;
use App\Models\MeetGreetEvents;
use App\Support\ListingQuery;
use App\Support\Spreadsheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MeetGreetEventsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListingQuery::from($request, ['event_date', 'event_name', 'ticket_sale_datetime'], 'event_date', [
            'type' => '',
            'date_from' => '',
            'date_to' => '',
        ]);

        $events = MeetGreetEvents::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nestedQuery) use ($filters): void {
                    $nestedQuery->where('event_name', 'like', "%{$filters['search']}%")
                        ->orWhere('purchase_link', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['type'] !== '', function ($query) use ($filters): void {
                $query->where('event_type', $filters['type']);
            })
            ->when($filters['date_from'] !== '', function ($query) use ($filters): void {
                $query->whereDate('event_date', '>=', $filters['date_from']);
            })
            ->when($filters['date_to'] !== '', function ($query) use ($filters): void {
                $query->whereDate('event_date', '<=', $filters['date_to']);
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('meet-greet-events.index', [
            'events' => $events,
            'filters' => $filters,
        ]);
    }

    public function export(): StreamedResponse
    {
        $events = MeetGreetEvents::query()->orderByDesc('event_date')->get();

        return Spreadsheet::download('meet-greet-events-'.now()->format('Ymd-His').'.xlsx', [
            'id',
            'event_name',
            'event_type',
            'event_date',
            'event_date_2',
            'ticket_sale_datetime',
            'purchase_link',
            'location',
        ], $events->map(static fn (MeetGreetEvents $event): array => [
            $event->id,
            $event->event_name,
            $event->event_type instanceof \BackedEnum ? $event->event_type->value : (string) $event->event_type,
            $event->event_date?->format('Y-m-d'),
            $event->event_date_2?->format('Y-m-d'),
            $event->ticket_sale_datetime?->format('Y-m-d H:i:s'),
            $event->purchase_link,
            $event->location,
        ]));
    }

    public function store(MeetGreetEventRequest $request): RedirectResponse
    {
        MeetGreetEvents::create($request->eventPayload());

        return redirect()->route('meet-greet-events.index')
            ->with('success', 'Meet & Greet event berhasil ditambahkan.');
    }

    public function update(MeetGreetEventRequest $request, MeetGreetEvents $meetGreetEvent): RedirectResponse
    {
        $meetGreetEvent->update($request->eventPayload());

        return redirect()->route('meet-greet-events.index')
            ->with('success', 'Meet & Greet event berhasil diupdate.');
    }

    public function destroy(MeetGreetEvents $meetGreetEvent): RedirectResponse
    {
        $meetGreetEvent->delete();

        return redirect()->route('meet-greet-events.index')
            ->with('success', 'Meet & Greet event berhasil dihapus.');
    }
}
