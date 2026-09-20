<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConcertEventRequest;
use App\Models\ConcertEvents;
use App\Support\ListingQuery;
use App\Support\Spreadsheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConcertEventsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListingQuery::from($request, ['event_date', 'event_name', 'purchase_link'], 'event_date', [
            'status' => '',
            'date_from' => '',
            'date_to' => '',
        ]);

        $events = ConcertEvents::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nestedQuery) use ($filters): void {
                    $nestedQuery->where('event_name', 'like', "%{$filters['search']}%")
                        ->orWhere('location', 'like', "%{$filters['search']}%")
                        ->orWhere('purchase_link', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['status'] !== '', function ($query) use ($filters): void {
                $query->where('status', $filters['status']);
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

        return view('concerts-events.index', [
            'events' => $events,
            'filters' => $filters,
        ]);
    }

    public function export(): StreamedResponse
    {
        $events = ConcertEvents::query()->orderByDesc('event_date')->get();

        return Spreadsheet::download('concert-events-'.now()->format('Ymd-His').'.xlsx', [
            'id',
            'event_name',
            'event_date',
            'location',
            'status',
            'purchase_link',
        ], $events->map(static fn (ConcertEvents $event): array => [
            $event->id,
            $event->event_name,
            $event->event_date?->format('Y-m-d'),
            $event->location,
            $event->status instanceof \BackedEnum ? $event->status->value : (string) $event->status,
            $event->purchase_link,
        ]));
    }

    public function store(ConcertEventRequest $request): RedirectResponse
    {
        ConcertEvents::create($request->validated());

        return redirect()->route('concert-events.index')
            ->with('success', 'Concert event berhasil ditambahkan.');
    }

    public function update(ConcertEventRequest $request, ConcertEvents $concertEvent): RedirectResponse
    {
        $concertEvent->update($request->validated());

        return redirect()->route('concert-events.index')
            ->with('success', 'Concert event berhasil diupdate.');
    }

    public function destroy(ConcertEvents $concertEvent): RedirectResponse
    {
        $concertEvent->delete();

        return redirect()->route('concert-events.index')
            ->with('success', 'Concert event berhasil dihapus.');
    }
}
