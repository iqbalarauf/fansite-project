<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Show Teater') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('show-teater.update', $showTeater->show_id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label for="show_id" class="block text-sm font-medium text-gray-700">Show ID</label>
                            <input type="number" id="show_id" value="{{ $showTeater->show_id }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-700" disabled />
                        </div>

                        <div>
                            <label for="show_date" class="block text-sm font-medium text-gray-700">Tanggal</label>
                            <input type="date" name="show_date" id="show_date" value="{{ old('show_date', $showTeater->show_date) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label for="setlist" class="block text-sm font-medium text-gray-700">Setlist</label>
                            <select name="setlist" id="setlist"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Pilih setlist</option>
                                @foreach ($setlistsWithUnitSongs as $setlist)
                                    <option value="{{ $setlist['name'] }}" {{ old('setlist', $showTeater->setlist) === $setlist['name'] ? 'selected' : '' }}>{{ $setlist['display_name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="unit_song" class="block text-sm font-medium text-gray-700">Unit Song</label>
                            <select name="unit_song" id="unit_song"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Pilih unit song</option>
                                @foreach ($setlistsWithUnitSongs as $setlist)
                                    @foreach ($setlist['unit_songs'] as $song)
                                        <option value="{{ $song['name'] }}" {{ old('unit_song', $showTeater->unit_song) === $song['name'] ? 'selected' : '' }}>{{ $setlist['display_name'] }} - {{ $song['display_name'] }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_global_center" id="is_global_center" value="1" {{ old('is_global_center', $showTeater->is_global_center) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                            <label for="is_global_center" class="text-sm font-medium text-gray-700">Global Center</label>
                        </div>

                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_us_center" id="is_us_center" value="1" {{ old('is_us_center', $showTeater->is_us_center) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                            <label for="is_us_center" class="text-sm font-medium text-gray-700">US Center</label>
                        </div>
                    </div>

                    <div>
                        <label for="is_the_show_has_event" class="block text-sm font-medium text-gray-700">Event</label>
                        <input type="text" name="is_the_show_has_event" id="is_the_show_has_event" value="{{ old('is_the_show_has_event', $showTeater->is_the_show_has_event) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Contoh: Opening" />
                    </div>

                    <div>
                        <label for="additional_information" class="block text-sm font-medium text-gray-700">Info Tambahan</label>
                        <input type="text" name="additional_information" id="additional_information" value="{{ old('additional_information', $showTeater->additional_information) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Keterangan tambahan" />
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <a href="{{ route('show-teater.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Batal</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
