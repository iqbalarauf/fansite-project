<?php

namespace App\Http\Controllers;

use App\Models\ConcertEvents;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConcertEventsController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $sortBy = $request->string('sort_by', 'event_date')->toString();
        $sortDir = $request->string('sort_dir', 'desc')->toString();
        $perPage = (int) $request->integer('per_page', 10);

        $allowedSorts = ['event_date', 'event_name', 'purchase_link'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'event_date';
        }

        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $events = ConcertEvents::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nestedQuery) use ($search): void {
                    $nestedQuery->where('event_name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('purchase_link', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function ($query) use ($status): void {
                $query->where('status', $status);
            })
            ->when($dateFrom !== '', function ($query) use ($dateFrom): void {
                $query->whereDate('event_date', '>=', $dateFrom);
            })
            ->when($dateTo !== '', function ($query) use ($dateTo): void {
                $query->whereDate('event_date', '<=', $dateTo);
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return view('concerts-events.index', [
            'events' => $events,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'location' => 'required|string|max:255',
            'status' => 'required|in:off-air,on-air,jkt48-event,media,ofc-event,brand',
            'purchase_link' => 'nullable|url|max:500',
        ]);

        ConcertEvents::create($validated);

        return redirect()->route('concert-events.index')
            ->with('success', 'Concert event berhasil ditambahkan.');
    }

    public function update(Request $request, ConcertEvents $concertEvent): RedirectResponse
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'location' => 'required|string|max:255',
            'status' => 'required|in:off-air,on-air,jkt48-event,media,ofc-event,brand',
            'purchase_link' => 'nullable|url|max:500',
        ]);

        $concertEvent->update($validated);

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
