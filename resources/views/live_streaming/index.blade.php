<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Live Streaming</h2>
            <a href="{{ route('live-streaming.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">Add Streaming</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white rounded-3xl shadow-sm border border-gray-200 p-6">
            <div class="space-y-4">
                @forelse ($liveStreams as $stream)
                    <div class="rounded-3xl border border-gray-200 p-5">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">{{ $stream->platform }}</h3>
                                <p class="text-sm text-gray-600">Live date: {{ $stream->live_date->format('d M Y H:i') }}</p>
                                @if ($stream->duration)
                                    <p class="text-sm text-gray-600">Duration: {{ $stream->duration }} minutes</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('live-streaming.edit', $stream) }}" class="text-indigo-600 hover:underline">Edit</a>
                                <form action="{{ route('live-streaming.destroy', $stream) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>
                        @if ($stream->additional_info)
                            <p class="mt-3 text-gray-700">{{ $stream->additional_info }}</p>
                        @endif
                    </div>
                @empty
                    <div class="rounded-3xl border border-gray-200 p-6 text-gray-600">No live streaming entries found.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
