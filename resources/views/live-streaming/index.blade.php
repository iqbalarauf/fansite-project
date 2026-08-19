<x-layouts::app :title="__('Live Streaming')">
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">Live Streaming</flux:heading>
                <flux:subheading>Kelola jadwal live streaming dan informasi tambahan</flux:subheading>
            </div>
            <flux:modal.trigger name="modal-create-live-stream">
                <flux:button variant="primary" icon="plus">Tambah Live Streaming</flux:button>
            </flux:modal.trigger>
        </div>

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

        <form method="GET" action="{{ route('live-streaming.index') }}" id="filter-form">
            <div class="admin-filter">
                <div class="admin-filter-search">
                    <flux:input
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Search platform atau additional info..."
                        icon="magnifying-glass"
                    />
                </div>

                <flux:input
                    name="date_from"
                    type="date"
                    value="{{ $filters['date_from'] }}"
                    class="w-40"
                />
                <flux:input
                    name="date_to"
                    type="date"
                    value="{{ $filters['date_to'] }}"
                    class="w-40"
                />

                <select
                    name="platform"
                    onchange="this.form.submit()"
                    class="admin-filter-select"
                >
                    <option value="">Semua Platform</option>
                    <option value="Showroom" {{ $filters['platform'] === 'Showroom' ? 'selected' : '' }}>Showroom</option>
                    <option value="IDN App" {{ $filters['platform'] === 'IDN App' ? 'selected' : '' }}>IDN App</option>
                </select>

                <select
                    name="sort_by"
                    onchange="this.form.submit()"
                    class="admin-filter-select"
                >
                    <option value="live_date" {{ $filters['sort_by'] === 'live_date' ? 'selected' : '' }}>Live Date</option>
                    <option value="platform" {{ $filters['sort_by'] === 'platform' ? 'selected' : '' }}>Platform</option>
                    <option value="duration" {{ $filters['sort_by'] === 'duration' ? 'selected' : '' }}>Duration</option>
                </select>

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

                <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}" />

                <flux:button type="submit" variant="outline">Cari</flux:button>

                @if ($filters['search'] || $filters['platform'] || $filters['date_from'] || $filters['date_to'])
                    <a href="{{ route('live-streaming.index') }}" class="admin-filter-reset">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-shell">
            <div class="overflow-x-auto">
                    <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">PLATFORM</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">LIVE DATE</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">DURATION (HH:MM)</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">ADDITIONAL INFO</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-500 dark:text-zinc-400">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @forelse ($liveStreams as $liveStream)
                            <tr class="admin-table-row">
                                <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">{{ $liveStream->platform }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $liveStream->live_date?->translatedFormat('d F Y') }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                    {{ $liveStream->duration !== null ? sprintf('%02d:%02d', intdiv($liveStream->duration, 60), $liveStream->duration % 60) : '–' }}
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $liveStream->additional_info ?: '–' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        class="admin-action-link"
                                        data-id="{{ $liveStream->id }}"
                                        data-platform="{{ $liveStream->platform }}"
                                        data-live-date="{{ $liveStream->live_date?->format('Y-m-d') }}"
                                        data-duration="{{ $liveStream->duration }}"
                                        data-additional-info="{{ $liveStream->additional_info }}"
                                        onclick="openEditLiveStreamModal(this)"
                                    >
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6.75a3.75 3.75 0 10-7.5 0v3.75m11.356-1.993l1.263 11.484A2.25 2.25 0 0118.631 22.5H5.37a2.25 2.25 0 01-2.238-2.504l1.263-11.484a2.25 2.25 0 012.238-1.996h10.734a2.25 2.25 0 012.238 1.996z"/></svg>
                                        <p class="font-medium">Belum ada data live streaming</p>
                                        <p class="text-xs">Tambah data baru dari tombol di kanan atas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('show-teater.partials.pagination', ['paginator' => $liveStreams, 'perPage' => $filters['per_page'], 'pageParam' => 'page'])
        </div>
    </div>

    <flux:modal name="modal-create-live-stream" class="md:w-[620px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Tambah Live Streaming</flux:heading>

            <form method="POST" action="{{ route('live-streaming.store') }}" class="space-y-5">
                @csrf

                <div>
                    <flux:label for="create-platform">Platform</flux:label>
                    <select
                        id="create-platform"
                        name="platform"
                        required
                        class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                    >
                        <option value="">Pilih Platform</option>
                        <option value="Showroom">Showroom</option>
                        <option value="IDN App">IDN App</option>
                    </select>
                </div>

                <div>
                    <flux:label for="create-live-date">Live Date</flux:label>
                    <flux:input id="create-live-date" name="live_date" type="date" required class="mt-1" />
                </div>

                <div>
                    <flux:label for="create-duration">Duration (minutes)</flux:label>
                    <flux:input id="create-duration" name="duration" type="number" min="0" class="mt-1" />
                </div>

                <div>
                    <flux:label for="create-additional-info">Additional Info</flux:label>
                    <flux:textarea id="create-additional-info" name="additional_info" rows="4" class="mt-1" />
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

    <flux:modal name="modal-edit-live-stream" class="md:w-[620px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Live Streaming</flux:heading>

            <form method="POST" id="edit-live-stream-form" action="" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <flux:label for="edit-platform">Platform</flux:label>
                    <select
                        id="edit-platform"
                        name="platform"
                        required
                        class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                    >
                        <option value="Showroom">Showroom</option>
                        <option value="IDN App">IDN App</option>
                    </select>
                </div>

                <div>
                    <flux:label for="edit-live-date">Live Date</flux:label>
                    <flux:input id="edit-live-date" name="live_date" type="date" required class="mt-1" />
                </div>

                <div>
                    <flux:label for="edit-duration">Duration (minutes)</flux:label>
                    <flux:input id="edit-duration" name="duration" type="number" min="0" class="mt-1" />
                </div>

                <div>
                    <flux:label for="edit-additional-info">Additional Info</flux:label>
                    <flux:textarea id="edit-additional-info" name="additional_info" rows="4" class="mt-1" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Update</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <script>
        function openEditLiveStreamModal(target) {
            const streamData = {
                id: target.dataset.id,
                platform: target.dataset.platform,
                live_date: target.dataset.liveDate,
                duration: target.dataset.duration,
                additional_info: target.dataset.additionalInfo,
            };

            document.getElementById('edit-live-stream-form').action = `{{ url('live-streaming') }}/${streamData.id}`;
            document.getElementById('edit-platform').value = streamData.platform || 'Showroom';
            document.getElementById('edit-live-date').value = streamData.live_date || '';
            document.getElementById('edit-duration').value = streamData.duration || '';
            document.getElementById('edit-additional-info').value = streamData.additional_info || '';

            Flux.modal('modal-edit-live-stream').show();
        }
    </script>
</x-layouts::app>
