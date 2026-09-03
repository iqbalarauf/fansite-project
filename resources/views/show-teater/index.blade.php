<x-layouts::app :title="__('Show Teater')">
    {{-- ================================================================
         Page Header
    ================================================================ --}}
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">Manage Show Teater</flux:heading>
                <flux:subheading>Kelola jadwal pertunjukan teater</flux:subheading>
            </div>
            <div class="admin-page-actions">
                {{-- Fetch Data Button --}}
                <flux:button
                    id="btn-fetch"
                    variant="filled"
                    icon="arrow-path"
                    class="bg-orange-500 hover:bg-orange-600 text-white border-0"
                    x-data
                    @click="fetchData()"
                    :disabled="auth()->user()?->isViewOnly()"
                >
                    Fetch Data
                </flux:button>

                {{-- Add Show Modal Trigger --}}
                <flux:modal.trigger name="modal-create-show">
                    <flux:button variant="primary" icon="plus">
                        Tambah Show
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        {{-- Success / Error Flash --}}
        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        {{-- ================================================================
             Filters & Search
        ================================================================ --}}
        <form method="GET" action="{{ route('show-teater.index') }}" id="filter-form">
            <div class="admin-filter">
                {{-- Search --}}
                <div class="admin-filter-search">
                    <flux:input
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Search by setlist, unit song..."
                        icon="magnifying-glass"
                    />
                </div>

                {{-- Date Range --}}
                <flux:input
                    name="date_from"
                    type="date"
                    value="{{ $filters['date_from'] }}"
                    placeholder="Date from"
                    class="w-40"
                />
                <flux:input
                    name="date_to"
                    type="date"
                    value="{{ $filters['date_to'] }}"
                    placeholder="Date to"
                    class="w-40"
                />

                {{-- Setlist Filter --}}
                <select
                    name="setlist"
                    onchange="this.form.submit()"
                    class="admin-filter-select"
                >
                    <option value="">Semua Setlist</option>
                    @foreach ($allSetlists as $setlist)
                        <option value="{{ $setlist }}" {{ $filters['setlist'] === $setlist ? 'selected' : '' }}>
                            {{ $setlist }}
                        </option>
                    @endforeach
                </select>

                {{-- Sort By --}}
                <select
                    name="sort_by"
                    onchange="this.form.submit()"
                    class="admin-filter-select"
                >
                    <option value="show_id" {{ $filters['sort_by'] === 'show_id' ? 'selected' : '' }}>Show ID</option>
                    <option value="show_date" {{ $filters['sort_by'] === 'show_date' ? 'selected' : '' }}>Tanggal</option>
                    <option value="setlist" {{ $filters['sort_by'] === 'setlist' ? 'selected' : '' }}>Setlist</option>
                </select>

                {{-- Sort Direction --}}
                <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] }}" id="sort-dir-input" />
                <button
                    type="button"
                    title="{{ $filters['sort_dir'] === 'asc' ? 'Ascending' : 'Descending' }}"
                    onclick="document.getElementById('sort-dir-input').value = '{{ $filters['sort_dir'] === 'asc' ? 'desc' : 'asc' }}'; document.getElementById('filter-form').submit();"
                    class="admin-filter-sort"
                >
                    @if ($filters['sort_dir'] === 'asc')
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>
                    @endif
                </button>

                {{-- Per Page --}}
                <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}" />

                {{-- Submit --}}
                <flux:button type="submit" variant="outline">Cari</flux:button>

                @if ($filters['search'] || $filters['setlist'] || $filters['date_from'] || $filters['date_to'])
                    <a href="{{ route('show-teater.index') }}" class="admin-filter-reset">Reset</a>
                @endif
            </div>
        </form>

        {{-- ================================================================
             Table
        ================================================================ --}}
        <div class="admin-table-shell">
            <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead class="admin-table-head">
                        <tr>
                            <th class="admin-table-header">SHOW ID</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">TANGGAL</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">SETLIST</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">UNIT SONG</th>
                            <th class="px-4 py-3 text-center font-medium text-zinc-500 dark:text-zinc-400">GLOBAL CENTER</th>
                            <th class="px-4 py-3 text-center font-medium text-zinc-500 dark:text-zinc-400">US CENTER</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">EVENT</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">INFO TAMBAHAN</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-500 dark:text-zinc-400">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @forelse ($shows as $show)
                            <tr class="admin-table-row">
                                <td class="px-4 py-3 font-medium text-blue-600 dark:text-blue-400">{{ $show->show_id }}</td>
                                @php
                                    $showDateDisplay = $show->show_date;

                                    try {
                                        $showDateDisplay = \Illuminate\Support\Carbon::parse($show->show_date)->translatedFormat('d F Y');
                                    } catch (\Throwable $exception) {
                                        // Keep original value if parsing fails.
                                    }
                                @endphp
                                <td class="px-4 py-3 text-blue-600 dark:text-blue-400">{{ $showDateDisplay }}</td>
                                <td class="px-4 py-3 text-blue-600 dark:text-blue-400">{{ $show->display_setlist ?: $show->setlist }}</td>
                                <td class="px-4 py-3 text-blue-600 dark:text-blue-400">{{ $show->display_unit_song ?: $show->unit_song }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($show->is_global_center)
                                        <flux:badge color="lime" size="sm">Yes</flux:badge>
                                    @else
                                        <span class="text-zinc-400">–</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($show->is_us_center)
                                        <flux:badge color="sky" size="sm">Yes</flux:badge>
                                    @else
                                        <span class="text-zinc-400">–</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300 max-w-32 truncate" title="{{ $show->is_the_show_has_event }}">
                                    {{ $show->is_the_show_has_event ?: '–' }}
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300 max-w-32 truncate" title="{{ $show->additional_information }}">
                                    {{ $show->additional_information ?: '–' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if (is_null($show->is_member_show))
                                        {{-- Unconfirmed: show confirm/reject icon buttons --}}
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                title="Konfirmasi Member Tampil"
                                                onclick="confirmMemberShow({{ $show->show_id }})"
                                                class="rounded-lg p-1.5 text-green-600 hover:bg-green-50 hover:text-green-700 dark:hover:bg-green-950/40"
                                                @disabled(auth()->user()?->isViewOnly())
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                            <button
                                                type="button"
                                                title="Member Tidak Tampil"
                                                onclick="rejectMemberShow({{ $show->show_id }})"
                                                class="rounded-lg p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/40"
                                                @disabled(auth()->user()?->isViewOnly())
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        {{-- Confirmed: show Edit button --}}
                                        <button
                                            type="button"
                                            class="admin-action-link"
                                            onclick="openEditModal({{ json_encode($show) }}, {{ json_encode($setlistsWithUnitSongs) }})"
                                        >
                                            Edit
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                                        </svg>
                                        <p class="font-medium">Tidak ada data show teater</p>
                                        <p class="text-xs">Tambah show baru atau ubah filter pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($shows->hasPages())
                <div class="flex items-center justify-between border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        Showing {{ $shows->firstItem() }} to {{ $shows->lastItem() }} of {{ $shows->total() }} results
                    </p>
                    <div class="flex items-center gap-1">
                        {{-- First --}}
                        <a
                            href="{{ $shows->url(1) }}"
                            class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-50 disabled:opacity-40 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 {{ $shows->onFirstPage() ? 'pointer-events-none opacity-40' : '' }}"
                        ><svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"/></svg></a>
                        {{-- Prev --}}
                        <a
                            href="{{ $shows->previousPageUrl() }}"
                            class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 {{ $shows->onFirstPage() ? 'pointer-events-none opacity-40' : '' }}"
                        ><svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg></a>

                        <span class="flex items-center gap-1.5 px-1 text-sm text-zinc-600 dark:text-zinc-300">
                            Page {{ $shows->currentPage() }} of {{ $shows->lastPage() }}
                        </span>

                        {{-- Next --}}
                        <a
                            href="{{ $shows->nextPageUrl() }}"
                            class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 {{ ! $shows->hasMorePages() ? 'pointer-events-none opacity-40' : '' }}"
                        ><svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg></a>
                        {{-- Last --}}
                        <a
                            href="{{ $shows->url($shows->lastPage()) }}"
                            class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 {{ ! $shows->hasMorePages() ? 'pointer-events-none opacity-40' : '' }}"
                        ><svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 4.5l7.5 7.5-7.5 7.5m6-15l7.5 7.5-7.5 7.5"/></svg></a>
                    </div>
                    {{-- Per page selector --}}
                    <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                        <span>Rows:</span>
                        @foreach ([10, 25, 50, 100] as $size)
                            <a
                                href="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}"
                                class="flex size-8 items-center justify-center rounded-lg border text-xs {{ $filters['per_page'] == $size ? 'border-blue-500 bg-blue-50 text-blue-600 font-medium dark:bg-blue-950 dark:text-blue-400' : 'border-zinc-200 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800' }}"
                            >{{ $size }}</a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        Showing {{ $shows->total() }} results
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- ================================================================
         CREATE MODAL
    ================================================================ --}}
    <flux:modal name="modal-create-show" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6"
            x-data="{
                setlists: {{ Js::from($setlistsWithUnitSongs) }},
                selectedSetlist: null,
                unitSongs: [],
                doubleUs: false,
                selectedUnitSong: '',
                selectedUnitSong2: '',
                selectSetlist(name) {
                    this.selectedSetlist = name;
                    const found = this.setlists.find(s => s.name === name);
                    this.unitSongs = found ? found.unit_songs : [];
                    this.selectedUnitSong = '';
                    this.selectedUnitSong2 = '';
                },
                get availableUnitSongs2() {
                    return this.unitSongs.filter(s => s.name !== this.selectedUnitSong);
                }
            }"
        >
            <flux:heading size="lg">Tambah Show Teater</flux:heading>

            <form method="POST" action="{{ route('show-teater.store') }}" class="space-y-5">
                @csrf

                {{-- Row: Show ID + Show Date --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:label>Show ID</flux:label>
                        <flux:input
                            name="show_id"
                            id="create-show-id"
                            type="number"
                            value="{{ $nextShowId }}"
                            disabled
                            class="mt-1 bg-zinc-50 dark:bg-zinc-800"
                        />
                        <input type="hidden" name="show_id" value="{{ $nextShowId }}" />
                    </div>
                    <div>
                        <flux:label for="create-show-date">Tanggal Show</flux:label>
                        <flux:input
                            name="show_date"
                            id="create-show-date"
                            type="date"
                            class="mt-1"
                            required
                        />
                    </div>
                </div>

                {{-- Setlist + Global Center checkbox --}}
                <div>
                    <div class="mb-1 flex items-center gap-3">
                        <flux:label for="create-setlist">Setlist</flux:label>
                        <label class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 cursor-pointer">
                            <input type="checkbox" name="is_global_center" value="1" class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600" />
                            Global Center
                        </label>
                    </div>
                    <select
                        name="setlist"
                        id="create-setlist"
                        required
                        x-on:change="selectSetlist($event.target.value)"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                    >
                        <option value="">Pilih Setlist</option>
                        @foreach ($setlistsWithUnitSongs as $setlist)
                            <option value="{{ $setlist['name'] }}">{{ $setlist['display_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Unit Song + Double US + US Center checkboxes --}}
                <div>
                    <div class="mb-1 flex items-center gap-3">
                        <flux:label>Unit Song</flux:label>
                        <label class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 cursor-pointer">
                            <input type="checkbox" name="double_us" value="1" x-model="doubleUs" class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600" />
                            Double US
                        </label>
                        <label class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 cursor-pointer">
                            <input type="checkbox" name="is_us_center" value="1" class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600" />
                            US Center
                        </label>
                    </div>

                    {{-- Single unit song dropdown --}}
                    <div x-show="!doubleUs">
                        <select
                            name="unit_song"
                            x-model="selectedUnitSong"
                            :disabled="!selectedSetlist"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                        >
                            <option value="">Pilih Unit Song</option>
                            <template x-for="song in unitSongs" :key="song.id">
                                <option :value="song.name" x-text="song.display_name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Double unit song dropdowns --}}
                    <div x-show="doubleUs" class="space-y-2">
                        <select
                            name="unit_song"
                            x-model="selectedUnitSong"
                            :disabled="!selectedSetlist"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                        >
                            <option value="">Pilih Unit Song 1</option>
                            <template x-for="song in unitSongs" :key="song.id">
                                <option :value="song.name" x-text="song.display_name"></option>
                            </template>
                        </select>
                        <select
                            name="unit_song_2"
                            x-model="selectedUnitSong2"
                            :disabled="!selectedSetlist"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                        >
                            <option value="">Pilih Unit Song 2</option>
                            <template x-for="song in availableUnitSongs2" :key="song.id">
                                <option :value="song.name" x-text="song.display_name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Event (optional) --}}
                <div>
                    <flux:label for="create-event">Event (opsional)</flux:label>
                    <flux:input
                        name="is_the_show_has_event"
                        id="create-event"
                        class="mt-1"
                        placeholder="STS Member, Milestone Show, Shonichi/Senshurakuu, Last Show"
                    />
                </div>

                {{-- Additional Information (optional) --}}
                <div>
                    <flux:label for="create-additional">Info Tambahan (opsional)</flux:label>
                    <flux:input
                        name="additional_information"
                        id="create-additional"
                        class="mt-1"
                        placeholder="Dapat diisi info tambahan: misal 'urutan blocking/warna seifuku', kageana, dll."
                    />
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- ================================================================
         EDIT MODAL
    ================================================================ --}}
    <flux:modal name="modal-edit-show" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6"
            id="edit-modal-root"
            x-data="{
                setlists: {{ Js::from($setlistsWithUnitSongs) }},
                selectedSetlist: '',
                unitSongs: [],
                doubleUs: false,
                selectedUnitSong: '',
                selectedUnitSong2: '',
                selectSetlist(name) {
                    this.selectedSetlist = name;
                    const found = this.setlists.find(s => s.name === name);
                    this.unitSongs = found ? found.unit_songs : [];
                    this.selectedUnitSong = '';
                    this.selectedUnitSong2 = '';
                },
                get availableUnitSongs2() {
                    return this.unitSongs.filter(s => s.name !== this.selectedUnitSong);
                }
            }"
        >
            <flux:heading size="lg">Edit Show Teater</flux:heading>

            <form method="POST" id="edit-form" action="" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:label>Show ID</flux:label>
                        <flux:input id="edit-show-id" type="number" disabled class="mt-1 bg-zinc-50 dark:bg-zinc-800" />
                    </div>
                    <div>
                        <flux:label for="edit-show-date">Tanggal Show</flux:label>
                        <flux:input name="show_date" id="edit-show-date" type="date" class="mt-1" required />
                    </div>
                </div>

                {{-- Setlist + Global Center --}}
                <div>
                    <div class="mb-1 flex items-center gap-3">
                        <flux:label for="edit-setlist">Setlist</flux:label>
                        <label class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 cursor-pointer">
                            <input type="checkbox" id="edit-is-global-center" name="is_global_center" value="1" class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600" />
                            Global Center
                        </label>
                    </div>
                    <select
                        name="setlist"
                        id="edit-setlist"
                        required
                        x-on:change="selectSetlist($event.target.value)"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                    >
                        <option value="">Pilih Setlist</option>
                        @foreach ($setlistsWithUnitSongs as $setlist)
                            <option value="{{ $setlist['name'] }}">{{ $setlist['display_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Unit Song + Double US + US Center --}}
                <div>
                    <div class="mb-1 flex items-center gap-3">
                        <flux:label>Unit Song</flux:label>
                        <label class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 cursor-pointer">
                            <input type="checkbox" name="double_us" id="edit-double-us" value="1" x-model="doubleUs" class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600" />
                            Double US
                        </label>
                        <label class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300 cursor-pointer">
                            <input type="checkbox" id="edit-is-us-center" name="is_us_center" value="1" class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600" />
                            US Center
                        </label>
                    </div>

                    {{-- Single unit song dropdown --}}
                    <div x-show="!doubleUs">
                        <select
                            name="unit_song"
                            id="edit-unit-song"
                            x-model="selectedUnitSong"
                            :disabled="!selectedSetlist"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                        >
                            <option value="">Pilih Unit Song</option>
                            <template x-for="song in unitSongs" :key="song.id">
                                <option :value="song.name" x-text="song.display_name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Double unit song dropdowns --}}
                    <div x-show="doubleUs" class="space-y-2">
                        <select
                            name="unit_song"
                            id="edit-unit-song-1"
                            x-model="selectedUnitSong"
                            :disabled="!selectedSetlist"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                        >
                            <option value="">Pilih Unit Song 1</option>
                            <template x-for="song in unitSongs" :key="song.id">
                                <option :value="song.name" x-text="song.display_name"></option>
                            </template>
                        </select>
                        <select
                            name="unit_song_2"
                            id="edit-unit-song-2"
                            x-model="selectedUnitSong2"
                            :disabled="!selectedSetlist"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                        >
                            <option value="">Pilih Unit Song 2</option>
                            <template x-for="song in availableUnitSongs2" :key="song.id">
                                <option :value="song.name" x-text="song.display_name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div>
                    <flux:label for="edit-event">Event (opsional)</flux:label>
                    <flux:input name="is_the_show_has_event" id="edit-event" class="mt-1" />
                </div>

                <div>
                    <flux:label for="edit-additional">Info Tambahan (opsional)</flux:label>
                    <flux:input name="additional_information" id="edit-additional" class="mt-1" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- ================================================================
         JavaScript
    ================================================================ --}}
    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';

        // ------ Fetch Data ------
        function fetchData() {
            const btn = document.getElementById('btn-fetch');
            btn.disabled = true;
            btn.textContent = 'Fetching...';

            fetch('{{ route('show-teater.fetch-manually') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Data berhasil di-fetch!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(() => alert('Terjadi kesalahan saat fetch data.'))
            .finally(() => { btn.disabled = false; btn.textContent = 'Fetch Data'; });
        }

        // ------ Confirm Member Show ------
        function confirmMemberShow(showId) {
            if (!confirm('Konfirmasi show #' + showId + ' sebagai Member Show?')) return;

            fetch(`{{ url('show-teater') }}/${showId}/confirm`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
                else alert('Error: ' + data.message);
            })
            .catch(() => alert('Terjadi kesalahan.'));
        }

        // ------ Reject Member Show ------
        function rejectMemberShow(showId) {
            if (!confirm('Hapus / tolak show #' + showId + '? Tindakan ini tidak bisa dibatalkan.')) return;

            fetch(`{{ url('show-teater') }}/${showId}/reject`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
                else alert('Error: ' + data.message);
            })
            .catch(() => alert('Terjadi kesalahan.'));
        }

        // ------ Open Edit Modal ------
        function openEditModal(show, setlistsWithUnitSongs) {
            // Set form action
            document.getElementById('edit-form').action = `{{ url('show-teater') }}/${show.show_id}`;

            // Populate fields
            document.getElementById('edit-show-id').value = show.show_id;

            // Convert date from stored format to yyyy-mm-dd for input[type=date]
            // show_date is stored as "Sabtu, 17 Agustus 2019" — try to parse or use raw
            const rawDate = show.show_date;
            // Try to interpret as a date; if it's already ISO-like use it directly
            const parsed = new Date(rawDate);
            if (!isNaN(parsed)) {
                document.getElementById('edit-show-date').value = parsed.toISOString().split('T')[0];
            } else {
                document.getElementById('edit-show-date').value = '';
            }

            document.getElementById('edit-is-global-center').checked = !!show.is_global_center;
            document.getElementById('edit-is-us-center').checked = !!show.is_us_center;
            document.getElementById('edit-event').value = show.is_the_show_has_event || '';
            document.getElementById('edit-additional').value = show.additional_information || '';

            // Setlist dropdown
            const setlistSel = document.getElementById('edit-setlist');
            setlistSel.value = show.setlist || '';

            // Trigger Alpine to update unit songs list
            const alpineRoot = document.getElementById('edit-modal-root');
            if (alpineRoot && alpineRoot._x_dataStack) {
                const alpineData = Alpine.$data(alpineRoot);
                const songs = (show.unit_song || '').split(', ');
                const isDouble = songs.length > 1;

                alpineData.selectedSetlist = show.setlist || '';
                const found = alpineData.setlists.find(s => s.name === show.setlist);
                alpineData.unitSongs = found ? found.unit_songs : [];

                alpineData.doubleUs = isDouble;

                // Wait for Alpine to be ready to consume the lists
                setTimeout(() => {
                    alpineData.selectedUnitSong = songs[0] || '';
                    alpineData.selectedUnitSong2 = songs[1] || '';
                }, 50);
            } else {
                // Fallback: populate unit songs manually
                const songs = (show.unit_song || '').split(', ');
                const isDouble = songs.length > 1;

                const editDoubleUs = document.getElementById('edit-double-us');
                if (editDoubleUs) {
                    editDoubleUs.checked = isDouble;
                }

                const found = setlistsWithUnitSongs.find(s => s.name === show.setlist);
                const unitSongSel = document.getElementById('edit-unit-song') || document.getElementById('edit-unit-song-1');
                if (unitSongSel) {
                    unitSongSel.innerHTML = '<option value="">Pilih Unit Song</option>';
                    if (found) {
                        found.unit_songs.forEach(song => {
                            const opt = document.createElement('option');
                            opt.value = song.name;
                            opt.textContent = song.display_name;
                            if (song.name === songs[0]) opt.selected = true;
                            unitSongSel.appendChild(opt);
                        });
                    }
                }

                const unitSongSel2 = document.getElementById('edit-unit-song-2');
                if (unitSongSel2) {
                    unitSongSel2.innerHTML = '<option value="">Pilih Unit Song 2</option>';
                    if (found && isDouble) {
                        found.unit_songs.forEach(song => {
                            if (song.name !== songs[0]) {
                                const opt = document.createElement('option');
                                opt.value = song.name;
                                opt.textContent = song.display_name;
                                if (song.name === songs[1]) opt.selected = true;
                                unitSongSel2.appendChild(opt);
                            }
                        });
                    }
                }
            }

            // Open modal via Flux
            Flux.modal('modal-edit-show').show();
        }
    </script>
</x-layouts::app>
