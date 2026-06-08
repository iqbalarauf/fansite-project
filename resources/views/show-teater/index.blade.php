<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Show Teater') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-600">Last fetch: {{ $lastFetchAt ? \Illuminate\Support\Carbon::parse($lastFetchAt)->format('d M Y H:i') : 'Belum tersedia' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('show-teater.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Tambah Show
                    </a>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('show-teater.index') }}" class="grid gap-4 lg:grid-cols-4">
                    <div>
                        <label for="show" class="block text-sm font-medium text-gray-700">Show ID</label>
                        <input id="show" name="show" type="text" value="{{ request('show') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Cari show id" />
                    </div>

                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                        <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700">Urutan</label>
                        <select id="sort" name="sort"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="asc" {{ request('sort', 'desc') === 'asc' ? 'selected' : '' }}>Show ID naik</option>
                            <option value="desc" {{ request('sort', 'desc') === 'desc' ? 'selected' : '' }}>Show ID turun</option>
                        </select>
                    </div>

                    <div class="lg:col-span-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex gap-2">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Filter
                            </button>
                            <a href="{{ route('show-teater.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                                Reset
                            </a>
                        </div>
                        <div class="text-sm text-gray-600">{{ $showTeaters->count() }} data ditemukan</div>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Show ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Setlist</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Song</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Global Center</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">US Center</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Has Event</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Info Tambahan</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($showTeaters as $showTeater)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->show_id }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->show_date }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->display_setlist }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->display_unit_song }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->is_global_center ? 'Ya' : 'Tidak' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->is_us_center ? 'Ya' : 'Tidak' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->is_the_show_has_event ? 'Ya' : 'Tidak' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->additional_information }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('show-teater.edit', $showTeater->show_id) }}"
                                            class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form action="{{ route('show-teater.destroy', $showTeater->show_id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Hapus show ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada data Show Teater untuk ditampilkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $showTeaters->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
