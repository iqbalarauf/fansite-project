<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Meet & Greet Events</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('flash'))
                <div class="rounded-3xl bg-white p-4 border border-gray-200 text-sm text-gray-700">{{ session('flash.banner') }}</div>
            @endif

            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900">Add New Event</h3>
                <form action="{{ route('meet-greet.store') }}" method="POST" class="mt-6 grid gap-6 lg:grid-cols-2">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event Name</label>
                        <input name="event_name" value="{{ old('event_name') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event Type</label>
                        <select name="event_type" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="meet-greet">Meet & Greet</option>
                            <option value="video-call">Video Call</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event Date</label>
                        <input name="event_date" type="date" value="{{ old('event_date') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Second Event Date</label>
                        <input name="event_date_2" type="date" value="{{ old('event_date_2') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ticket Sale Date</label>
                        <input name="ticket_sale_datetime" type="datetime-local" value="{{ old('ticket_sale_datetime') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Purchase Link</label>
                        <input name="purchase_link" value="{{ old('purchase_link') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div class="lg:col-span-2 text-right">
                        <x-primary-button>Add Event</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 overflow-x-auto">
                <h3 class="text-lg font-semibold text-gray-900">Event List</h3>
                <table class="min-w-full divide-y divide-gray-200 text-sm mt-5">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Event Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Ticket Sale</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($events as $event)
                            <tr>
                                <td class="px-4 py-4">{{ $event['event_name'] }}</td>
                                <td class="px-4 py-4">{{ $event['event_type'] === 'video-call' ? 'Video Call' : 'Meet & Greet' }}</td>
                                <td class="px-4 py-4">{{ $event['event_date'] }}</td>
                                <td class="px-4 py-4">{{ $event['ticket_sale_datetime'] ?? '-' }}</td>
                                <td class="px-4 py-4">
                                    <form action="{{ route('meet-greet.destroy', $event['id']) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-center text-gray-500" colspan="5">No events found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
