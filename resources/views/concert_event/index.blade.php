<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Concert Events</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-3xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900">Add New Concert Event</h3>
                <form action="{{ route('concert-events.store') }}" method="POST" class="mt-6 grid gap-4 lg:grid-cols-2">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event Name</label>
                        <input name="event_name" type="text" value="{{ old('event_name') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input name="event_date" type="date" value="{{ old('event_date') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <input name="location" type="text" value="{{ old('location') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="off-air" {{ old('status') === 'off-air' ? 'selected' : '' }}>Off-air</option>
                            <option value="on-air" {{ old('status') === 'on-air' ? 'selected' : '' }}>On-air</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Purchase Link</label>
                        <input name="purchase_link" type="url" value="{{ old('purchase_link') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div class="lg:col-span-2 text-right">
                        <x-primary-button>Add Event</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-3xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900">Existing Events</h3>
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Event</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Date</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Location</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($events as $event)
                                <tr>
                                    <td class="px-4 py-4">{{ $event['event_name'] }}</td>
                                    <td class="px-4 py-4">{{ $event['event_date'] }}</td>
                                    <td class="px-4 py-4">{{ $event['location'] }}</td>
                                    <td class="px-4 py-4 capitalize">{{ $event['status'] }}</td>
                                    <td class="px-4 py-4 space-x-2">
                                        <form action="{{ route('concert-events.destroy', $event['id']) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-4 text-gray-500" colspan="5">No concert events found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
