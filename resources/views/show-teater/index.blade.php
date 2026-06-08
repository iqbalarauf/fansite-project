<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Show Teater') }}
        </h2>
    </x-slot>

    <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-end mb-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400 text-right">
                        Last Fetch
                    </span>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Header Section -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Manage Show Teater
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Kelola jadwal pertunjukan teater
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex flex-col items-end gap-1">
                                    <button @click="fetchManually" :disabled="isFetching"
                                        class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-orange-600 text-white hover:bg-orange-700 focus:outline-none focus:bg-orange-700 disabled:opacity-50 disabled:pointer-events-none">
                                        <svg v-if="isFetching" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="size-4 animate-spin">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.995-1.465" />
                                        </svg>
                                        <span v-else>Fetch Data</span>
                                    </button>
                                </div>
                                <button @click="openAddModal"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                                    Tambahkan Show
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <input v-model="searchTerm" type="text" placeholder="Search by setlist, unit song..."
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <select v-model="selectedSetlist"
                                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="all">Semua Setlist</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Show ID
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Setlist
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Unit Song
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Global Center
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        US Center
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Event
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Info Tambahan
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($showTeaters as $showTeater)
                                <tr
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700">

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $showTeater->show_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $showTeater->show_date }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                       {{ $showTeater->setlist }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $showTeater->unit_song }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $showTeater->is_global_center ? 'Ya' : 'Tidak' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $showTeater->is_us_center ? 'Ya' : 'Tidak' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $showTeater->is_the_show_has_event ? 'Ya' : 'Tidak' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $showTeater->additional_information }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <!-- If is_member_show is NULL, show Yes/No buttons -->
                                        <div v-if="show.is_member_show === null" class="flex gap-2 justify-end">
                                            <button @click="confirmMemberShow(show.show_id)"
                                                :disabled="processingShowId === show.show_id"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                                title="Confirm as member show">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                            <button @click="rejectMemberShow(show.show_id)"
                                                :disabled="processingShowId === show.show_id"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                                title="Reject and delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- Otherwise show Edit button -->
                                        <button v-else @click="openEditModal(show)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr v-if="paginatedShows.length === 0">
                                    <td colspan="9"
                                        class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No shows found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('show-teater.index') }}" class="grid gap-4 lg:grid-cols-4">
                    <div>
                        <label for="show" class="block text-sm font-medium text-gray-700">Show ID</label>
                        <input id="show" name="show" type="text" value="{{ request('show') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Cari show id" />
                    </div>

                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                        <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700">Urutan</label>
                        <select id="sort" name="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="asc" {{ request('sort', 'asc') === 'asc' ? 'selected' : '' }}>{{ __('Show ID naik') }}</option>
                            <option value="desc" {{ request('sort') === 'desc' ? 'selected' : '' }}>{{ __('Show ID turun') }}</option>
                        </select>
                    </div>

                    <div class="lg:col-span-4 flex items-end justify-between space-x-3">
                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Filter</button>
                            <a href="{{ route('show-teater.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-200">Reset</a>
                        </div>
                        <div class="text-sm text-gray-600">{{ $showTeaters->total() }} data ditemukan</div>
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
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($showTeaters as $showTeater)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->show_id }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->show_date }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->setlist }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->unit_song }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->is_global_center ? 'Ya' : 'Tidak' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->is_us_center ? 'Ya' : 'Tidak' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->is_the_show_has_event ? 'Ya' : 'Tidak' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $showTeater->additional_information }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada data Show Teater untuk ditampilkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
