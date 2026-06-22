<x-layouts::app :title="__('Setlist & Unit Song')">
    @php
        $activeTab = $filters['tab'] ?? 'setlist';
    @endphp

    <div class="flex flex-col gap-6">

        {{-- ================================================================
             Page Header
        ================================================================ --}}
        <div class="flex items-start justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">Setlist &amp; Unit Song</flux:heading>
                <flux:subheading>Kelola data setlist dan unit song pertunjukan teater</flux:subheading>
            </div>
            <flux:modal.trigger name="modal-create-category">
                <flux:button variant="primary" icon="plus">Tambah Kategori</flux:button>
            </flux:modal.trigger>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ================================================================
             Tabs
        ================================================================ --}}
        <div class="flex gap-1 border-b border-zinc-200 dark:border-zinc-700">
            <a
                href="{{ route('show-teater.categories.index', array_merge(request()->except(['tab', 'setlist_page', 'unit_page']), ['tab' => 'setlist'])) }}"
                class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                    {{ $activeTab === 'setlist'
                        ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                        : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
            >
                Setlist
                <span class="ml-1.5 rounded-full bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    {{ $setlists->total() }}
                </span>
            </a>
            <a
                href="{{ route('show-teater.categories.index', array_merge(request()->except(['tab', 'setlist_page', 'unit_page']), ['tab' => 'unit_song'])) }}"
                class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                    {{ $activeTab === 'unit_song'
                        ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                        : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
            >
                Unit Song
                <span class="ml-1.5 rounded-full bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    {{ $unitSongs->total() }}
                </span>
            </a>
        </div>

        {{-- ================================================================
             Filters
        ================================================================ --}}
        <form method="GET" action="{{ route('show-teater.categories.index') }}" id="filter-form">
            <input type="hidden" name="tab" value="{{ $activeTab }}" />
            <div class="flex flex-wrap items-center gap-3">
                {{-- Search --}}
                <div class="flex-1 min-w-60">
                    <flux:input
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="{{ $activeTab === 'setlist' ? 'Search nama setlist...' : 'Search nama unit song, setlist...' }}"
                        icon="magnifying-glass"
                    />
                </div>

                {{-- Setlist filter (only on unit song tab) --}}
                @if ($activeTab === 'unit_song')
                    <select
                        name="setlist"
                        onchange="this.form.submit()"
                        class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                    >
                        <option value="">Semua Setlist</option>
                        @foreach ($allSetlistsForFilter as $sl)
                            <option value="{{ $sl->id }}" {{ (string) $filters['setlist'] === (string) $sl->id ? 'selected' : '' }}>
                                {{ $sl->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                {{-- Sort By --}}
                <select
                    name="sort_by"
                    onchange="this.form.submit()"
                    class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                >
                    <option value="id" {{ $filters['sort_by'] === 'id' ? 'selected' : '' }}>ID</option>
                    <option value="name" {{ $filters['sort_by'] === 'name' ? 'selected' : '' }}>Nama</option>
                    <option value="jp_name" {{ $filters['sort_by'] === 'jp_name' ? 'selected' : '' }}>Nama (JP)</option>
                </select>

                {{-- Sort Direction Toggle --}}
                <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] }}" id="sort-dir-input" />
                <button
                    type="button"
                    title="{{ $filters['sort_dir'] === 'asc' ? 'Ascending' : 'Descending' }}"
                    onclick="document.getElementById('sort-dir-input').value = '{{ $filters['sort_dir'] === 'asc' ? 'desc' : 'asc' }}'; document.getElementById('filter-form').submit();"
                    class="flex items-center justify-center rounded-lg border border-zinc-200 bg-white p-2 text-zinc-600 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                >
                    @if ($filters['sort_dir'] === 'asc')
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>
                    @endif
                </button>

                <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}" />

                <flux:button type="submit" variant="outline">Cari</flux:button>

                @if ($filters['search'] || $filters['setlist'])
                    <a href="{{ route('show-teater.categories.index', ['tab' => $activeTab]) }}" class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">Reset</a>
                @endif
            </div>
        </form>

        {{-- ================================================================
             SETLIST TABLE
        ================================================================ --}}
        @if ($activeTab === 'setlist')
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400 w-16">NO</th>
                                <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">NAMA SETLIST</th>
                                <th class="px-4 py-3 text-center font-medium text-zinc-500 dark:text-zinc-400 w-28">STATUS</th>
                                <th class="px-4 py-3 text-right font-medium text-zinc-500 dark:text-zinc-400 w-32">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($setlists as $item)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors" id="row-setlist-{{ $item->id }}">
                                    <td class="px-4 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ $loop->iteration + ($setlists->firstItem() - 1) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $item->name }}</div>
                                        @if ($item->jp_name)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $item->jp_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span id="badge-setlist-{{ $item->id }}">
                                            @if ($item->is_active)
                                                <flux:badge color="lime" size="sm">Active</flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm">Inactive</flux:badge>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button
                                                type="button"
                                                class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                onclick="openEditSetlistModal({{ json_encode($item) }})"
                                            >Edit</button>
                                            <button
                                                type="button"
                                                id="toggle-btn-setlist-{{ $item->id }}"
                                                class="text-sm font-medium {{ $item->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800' }}"
                                                onclick="toggleStatus({{ $item->id }}, 'setlist', {{ $item->is_active }})"
                                            >{{ $item->is_active ? 'Inactive' : 'Active' }}</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.661a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.661A2.25 2.25 0 009 15.553z"/></svg>
                                            <p class="font-medium">Belum ada data setlist</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('show-teater.partials.pagination', ['paginator' => $setlists, 'perPage' => $filters['per_page'], 'pageParam' => 'setlist_page'])
            </div>
        @endif

        {{-- ================================================================
             UNIT SONG TABLE
        ================================================================ --}}
        @if ($activeTab === 'unit_song')
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400 w-16">NO</th>
                                <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">SETLIST</th>
                                <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">NAMA UNIT SONG</th>
                                <th class="px-4 py-3 text-center font-medium text-zinc-500 dark:text-zinc-400 w-28">STATUS</th>
                                <th class="px-4 py-3 text-right font-medium text-zinc-500 dark:text-zinc-400 w-32">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($unitSongs as $item)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors" id="row-unit-{{ $item->id }}">
                                    <td class="px-4 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ $loop->iteration + ($unitSongs->firstItem() - 1) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-zinc-700 dark:text-zinc-300">
                                            {{ $item->setlist_name }}{{ $item->setlist_jp_name ? ' ('.$item->setlist_jp_name.')' : '' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $item->name }}</div>
                                        @if ($item->jp_name)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $item->jp_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span id="badge-unit-{{ $item->id }}">
                                            @if ($item->is_active)
                                                <flux:badge color="lime" size="sm">Active</flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm">Inactive</flux:badge>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button
                                                type="button"
                                                class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                onclick="openEditUnitSongModal({{ json_encode($item) }}, {{ Js::from($allSetlists) }})"
                                            >Edit</button>
                                            <button
                                                type="button"
                                                id="toggle-btn-unit-{{ $item->id }}"
                                                class="text-sm font-medium {{ $item->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800' }}"
                                                onclick="toggleStatus({{ $item->id }}, 'unit', {{ $item->is_active }})"
                                            >{{ $item->is_active ? 'Inactive' : 'Active' }}</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.661a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.661A2.25 2.25 0 009 15.553z"/></svg>
                                            <p class="font-medium">Belum ada data unit song</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('show-teater.partials.pagination', ['paginator' => $unitSongs, 'perPage' => $filters['per_page'], 'pageParam' => 'unit_page'])
            </div>
        @endif

    </div>

    {{-- ================================================================
         CREATE MODAL
    ================================================================ --}}
    <flux:modal name="modal-create-category" class="md:w-[520px]" variant="flyout">
        <div class="space-y-6"
            x-data="{
                type: '{{ old('type', 'setlist') }}',
                setlists: {{ Js::from($allSetlists) }},
                selectedSetlistId: '{{ old('setlist_id', '') }}',
                get hasSetlist() { return this.selectedSetlistId !== '' && this.selectedSetlistId !== null; }
            }"
        >
            <flux:heading size="lg">Tambah Kategori</flux:heading>

            <form method="POST" action="{{ route('show-teater.categories.store') }}" class="space-y-5">
                @csrf

                {{-- Type Radio --}}
                <div>
                    <flux:label class="mb-2 block">Tipe Kategori</flux:label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                name="type"
                                value="setlist"
                                x-model="type"
                                class="text-blue-600 border-zinc-300 dark:border-zinc-600"
                            />
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Setlist</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                name="type"
                                value="unit_song"
                                x-model="type"
                                class="text-blue-600 border-zinc-300 dark:border-zinc-600"
                            />
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Unit Song</span>
                        </label>
                    </div>
                </div>

                {{-- SETLIST FIELDS --}}
                <div x-show="type === 'setlist'" x-transition class="space-y-4">
                    <div>
                        <flux:label for="create-setlist-name">Nama Setlist <span class="text-red-500">*</span></flux:label>
                        <flux:input
                            name="name"
                            id="create-setlist-name"
                            class="mt-1"
                            placeholder="Contoh: Pajama Drive"
                            value="{{ old('name') }}"
                            x-bind:required="type === 'setlist'"
                        />
                    </div>
                    <div>
                        <flux:label for="create-setlist-jp">Nama Setlist (Bahasa Jepang)</flux:label>
                        <flux:input
                            name="jp_name"
                            id="create-setlist-jp"
                            class="mt-1"
                            placeholder="Opsional"
                            value="{{ old('jp_name') }}"
                        />
                    </div>
                </div>

                {{-- UNIT SONG FIELDS --}}
                <div x-show="type === 'unit_song'" x-transition class="space-y-4">
                    {{-- Setlist dropdown --}}
                    <div>
                        <flux:label for="create-us-setlist">Setlist <span class="text-red-500">*</span></flux:label>
                        <select
                            name="setlist_id"
                            id="create-us-setlist"
                            x-model="selectedSetlistId"
                            x-bind:required="type === 'unit_song'"
                            class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                        >
                            <option value="">Pilih Setlist</option>
                            <template x-for="s in setlists" :key="s.id">
                                <option :value="s.id" x-text="s.name + (s.jp_name ? ' (' + s.jp_name + ')' : '')"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Unit Song name --}}
                    <div>
                        <flux:label for="create-us-name">Nama Unit Song <span class="text-red-500">*</span></flux:label>
                        <flux:input
                            name="name"
                            id="create-us-name"
                            class="mt-1"
                            placeholder="Contoh: Junjou Shugi"
                            value="{{ old('name') }}"
                            x-bind:disabled="!hasSetlist"
                            x-bind:required="type === 'unit_song'"
                        />
                        <p x-show="!hasSetlist" class="mt-1 text-xs text-zinc-400">Pilih setlist terlebih dahulu</p>
                    </div>

                    {{-- Unit Song JP name --}}
                    <div>
                        <flux:label for="create-us-jp">Nama Unit Song (Bahasa Jepang)</flux:label>
                        <flux:input
                            name="jp_name"
                            id="create-us-jp"
                            class="mt-1"
                            placeholder="Opsional"
                            value="{{ old('jp_name') }}"
                            x-bind:disabled="!hasSetlist"
                        />
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- ================================================================
         EDIT SETLIST MODAL
    ================================================================ --}}
    <flux:modal name="modal-edit-setlist" class="md:w-[480px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Setlist</flux:heading>
            <form method="POST" id="edit-setlist-form" action="" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="setlist" />
                <div>
                    <flux:label for="edit-setlist-name">Nama Setlist <span class="text-red-500">*</span></flux:label>
                    <flux:input name="name" id="edit-setlist-name" class="mt-1" required />
                </div>
                <div>
                    <flux:label for="edit-setlist-jp">Nama Setlist (Bahasa Jepang)</flux:label>
                    <flux:input name="jp_name" id="edit-setlist-jp" class="mt-1" placeholder="Opsional" />
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- ================================================================
         EDIT UNIT SONG MODAL
    ================================================================ --}}
    <flux:modal name="modal-edit-unit-song" class="md:w-[480px]" variant="flyout"
        x-data="{
            setlists: [],
            selectedSetlistId: '',
            get hasSetlist() { return this.selectedSetlistId !== '' && this.selectedSetlistId !== null; }
        }"
    >
        <div class="space-y-6">
            <flux:heading size="lg">Edit Unit Song</flux:heading>
            <form method="POST" id="edit-unit-form" action="" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="unit_song" />
                <div>
                    <flux:label for="edit-unit-setlist">Setlist <span class="text-red-500">*</span></flux:label>
                    <select
                        name="setlist_id"
                        id="edit-unit-setlist"
                        x-model="selectedSetlistId"
                        required
                        class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                    >
                        <option value="">Pilih Setlist</option>
                        <template x-for="s in setlists" :key="s.id">
                            <option :value="String(s.id)" x-text="s.name + (s.jp_name ? ' (' + s.jp_name + ')' : '')"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <flux:label for="edit-unit-name">Nama Unit Song <span class="text-red-500">*</span></flux:label>
                    <flux:input name="name" id="edit-unit-name" class="mt-1" required x-bind:disabled="!hasSetlist" />
                </div>
                <div>
                    <flux:label for="edit-unit-jp">Nama Unit Song (Bahasa Jepang)</flux:label>
                    <flux:input name="jp_name" id="edit-unit-jp" class="mt-1" placeholder="Opsional" x-bind:disabled="!hasSetlist" />
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- ================================================================
         JavaScript
    ================================================================ --}}
    <script>
        const CSRF = '{{ csrf_token() }}';

        function openEditSetlistModal(item) {
            document.getElementById('edit-setlist-form').action = `{{ url('show-teater/categories') }}/${item.id}`;
            document.getElementById('edit-setlist-name').value = item.name || '';
            document.getElementById('edit-setlist-jp').value = item.jp_name || '';
            Flux.modal('modal-edit-setlist').show();
        }

        function openEditUnitSongModal(item, setlists) {
            document.getElementById('edit-unit-form').action = `{{ url('show-teater/categories') }}/${item.id}`;

            // Populate Alpine data on the modal
            const modal = document.querySelector('[data-flux-modal="modal-edit-unit-song"]');
            if (modal) {
                const alpineData = Alpine.$data(modal);
                alpineData.setlists = setlists;
                alpineData.selectedSetlistId = String(item.setlist_id || '');
            }

            // Fallback: populate setlist select directly
            const sel = document.getElementById('edit-unit-setlist');
            sel.innerHTML = '<option value="">Pilih Setlist</option>';
            setlists.forEach(s => {
                const opt = document.createElement('option');
                opt.value = String(s.id);
                opt.textContent = s.name + (s.jp_name ? ' (' + s.jp_name + ')' : '');
                if (String(s.id) === String(item.setlist_id)) opt.selected = true;
                sel.appendChild(opt);
            });

            document.getElementById('edit-unit-name').value = item.name || '';
            document.getElementById('edit-unit-jp').value = item.jp_name || '';
            Flux.modal('modal-edit-unit-song').show();
        }

        function toggleStatus(id, type, currentStatus) {
            const label = currentStatus ? 'menonaktifkan' : 'mengaktifkan';
            if (!confirm(`Yakin ingin ${label} kategori ini?`)) return;

            const btn = document.getElementById(`toggle-btn-${type}-${id}`);
            const badge = document.getElementById(`badge-${type}-${id}`);
            btn.disabled = true;

            fetch(`{{ url('show-teater/categories') }}/${id}/toggle-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const isActive = data.is_active;

                    // Update badge
                    badge.innerHTML = isActive
                        ? `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-lime-100 text-lime-700 dark:bg-lime-900/30 dark:text-lime-400">Active</span>`
                        : `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>`;

                    // Update button text + color
                    btn.textContent = isActive ? 'Inactive' : 'Active';
                    btn.className = `text-sm font-medium ${isActive ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800'}`;
                    btn.disabled = false;

                    // Store new state
                    btn.setAttribute('onclick', `toggleStatus(${id}, '${type}', ${isActive})`);
                } else {
                    alert('Gagal mengubah status.');
                    btn.disabled = false;
                }
            })
            .catch(() => { alert('Terjadi kesalahan.'); btn.disabled = false; });
        }
    </script>
</x-layouts::app>
