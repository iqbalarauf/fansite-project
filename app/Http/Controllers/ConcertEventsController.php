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
            'Event Name',
            'Date',
            'Location',
            'Status',
            'Purchase Link',
        ], $events->map(function (ConcertEvents $event): array {
            $status = $event->status instanceof \BackedEnum ? $event->status->value : (string) $event->status;

            return [
                $event->event_name,
                $event->event_date?->translatedFormat('d F Y'),
                $event->location,
                $this->statusLabel($status),
                $event->purchase_link ?: '–',
            ];
        }));
    }

    /**
     * Label status yang sama dengan badge pada tabel.
     */
    private function statusLabel(string $status): string
    {
        return match ($status) {
            'on-air' => 'On-Air',
            'off-air' => 'Off-Air',
            'jkt48-event' => 'JKT48 Event',
            'media' => 'Media',
            'ofc-event' => 'OFC Event',
            'brand' => 'Brand',
            default => ucfirst($status),
        };
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
