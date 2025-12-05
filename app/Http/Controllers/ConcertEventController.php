<?php

namespace App\Http\Controllers;

use App\Models\ConcertEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConcertEventController extends Controller
{
    public function index()
    {
        $events = ConcertEvent::orderBy('event_date', 'desc')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_name' => $event->event_name,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'location' => $event->location,
                    'status' => $event->status,
                    'purchase_link' => $event->purchase_link,
                ];
            })
            ->toArray();

        return Inertia::render('ConcertEvent/Index', [
            'events' => $events,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'location' => 'required|string|max:255',
            'status' => 'required|in:off-air,on-air',
            'purchase_link' => 'nullable|url|max:500',
        ]);

        ConcertEvent::create($validated);

        return redirect()->route('concert-events.index')
            ->with('flash', ['banner' => 'Concert event created successfully.', 'bannerStyle' => 'success']);
    }

    public function update(Request $request, ConcertEvent $concertEvent)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'location' => 'required|string|max:255',
            'status' => 'required|in:off-air,on-air',
            'purchase_link' => 'nullable|url|max:500',
        ]);

        $concertEvent->update($validated);

        return redirect()->route('concert-events.index')
            ->with('flash', ['banner' => 'Concert event updated successfully.', 'bannerStyle' => 'success']);
    }

    public function destroy(ConcertEvent $concertEvent)
    {
        $concertEvent->delete();

        return redirect()->route('concert-events.index')
            ->with('flash', ['banner' => 'Concert event deleted successfully.', 'bannerStyle' => 'success']);
    }
}
