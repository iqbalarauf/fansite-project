<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Live Streaming</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white rounded-3xl shadow-sm border border-gray-200 p-6">
            <form action="{{ route('live-streaming.store') }}" method="POST">
                @csrf
                <div class="grid gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Platform</label>
                        <select name="platform" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="IDN App">IDN App</option>
                            <option value="Showroom">Showroom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Live Date</label>
                        <input type="datetime-local" name="live_date" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                        <input type="number" name="duration" min="0" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Additional Info</label>
                        <textarea name="additional_info" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('live-streaming.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-gray-700">Cancel</a>
                        <x-primary-button>Save</x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
