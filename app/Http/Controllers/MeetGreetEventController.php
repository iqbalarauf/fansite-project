<?php

namespace App\Http\Controllers;

use App\Models\MeetGreetEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeetGreetEventController extends Controller
{
    public function index()
    {
        $events = MeetGreetEvent::orderBy('event_date', 'desc')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_name' => $event->event_name,
                    'event_type' => $event->event_type,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'event_date_2' => $event->event_date_2 ? $event->event_date_2->format('Y-m-d') : null,
                    'ticket_sale_datetime' => $event->ticket_sale_datetime ? $event->ticket_sale_datetime->format('Y-m-d\\TH:i') : null,
                    'purchase_link' => $event->purchase_link,
                ];
            })
            ->toArray();

        return Inertia::render('MeetGreet/Index', [
            'events' => $events,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_type' => 'required|in:meet-greet,video-call',
            'event_date' => 'required|date',
            'event_date_2' => 'nullable|date|after_or_equal:event_date',
            'ticket_sale_datetime' => 'nullable|date',
            'purchase_link' => 'nullable|url|max:500',
        ]);

        MeetGreetEvent::create($validated);

        return redirect()->route('meet-greet.index')
            ->with('flash', ['banner' => 'Meet & Greet event created successfully.', 'bannerStyle' => 'success']);
    }

    public function update(Request $request, MeetGreetEvent $meetGreet)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_type' => 'required|in:meet-greet,video-call',
            'event_date' => 'required|date',
            'event_date_2' => 'nullable|date|after_or_equal:event_date',
            'ticket_sale_datetime' => 'nullable|date',
            'purchase_link' => 'nullable|url|max:500',
        ]);

        $meetGreet->update($validated);

        return redirect()->route('meet-greet.index')
            ->with('flash', ['banner' => 'Meet & Greet event updated successfully.', 'bannerStyle' => 'success']);
    }

    public function destroy(MeetGreetEvent $meetGreet)
    {
        $meetGreet->delete();

        return redirect()->route('meet-greet.index')
            ->with('flash', ['banner' => 'Meet & Greet event deleted successfully.', 'bannerStyle' => 'success']);
    }
}
