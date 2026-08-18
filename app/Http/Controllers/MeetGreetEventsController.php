<?php

namespace App\Http\Controllers;

use App\Models\MeetGreetEvents;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetGreetEventsController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $type = $request->string('type')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $sortBy = $request->string('sort_by', 'event_date')->toString();
        $sortDir = $request->string('sort_dir', 'desc')->toString();
        $perPage = (int) $request->integer('per_page', 10);

        $allowedSorts = ['event_date', 'event_name', 'ticket_sale_datetime'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'event_date';
        }

        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $events = MeetGreetEvents::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nestedQuery) use ($search): void {
                    $nestedQuery->where('event_name', 'like', "%{$search}%")
                        ->orWhere('purchase_link', 'like', "%{$search}%");
                });
            })
            ->when($type !== '', function ($query) use ($type): void {
                $query->where('event_type', $type);
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

        return view('meet-greet-events.index', [
            'events' => $events,
            'filters' => [
                'search' => $search,
                'type' => $type,
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
            'event_type' => 'required|in:meet-greet,video-call',
            'event_date' => 'required|date',
            'event_date_2' => 'nullable|required_if:event_type,video-call|date|after_or_equal:event_date',
            'ticket_sale_datetime' => 'nullable|date',
            'purchase_link' => 'nullable|url|max:500',
            'location' => 'required|string|max:255',
        ]);

        if ($validated['event_type'] !== 'video-call') {
            $validated['event_date_2'] = null;
        }

        MeetGreetEvents::create($validated);

        return redirect()->route('meet-greet-events.index')
            ->with('success', 'Meet & Greet event berhasil ditambahkan.');
    }

    public function update(Request $request, MeetGreetEvents $meetGreetEvent): RedirectResponse
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_type' => 'required|in:meet-greet,video-call',
            'event_date' => 'required|date',
            'event_date_2' => 'nullable|required_if:event_type,video-call|date|after_or_equal:event_date',
            'ticket_sale_datetime' => 'nullable|date',
            'purchase_link' => 'nullable|url|max:500',
            'location' => 'required|string|max:255',
        ]);

        if ($validated['event_type'] !== 'video-call') {
            $validated['event_date_2'] = null;
        }

        $meetGreetEvent->update($validated);

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
