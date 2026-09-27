<x-layouts::app :title="__('Live Streaming')">
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">Live Streaming</flux:heading>
                <flux:subheading>Kelola jadwal live streaming dan informasi tambahan</flux:subheading>
            </div>
            <div class="admin-page-actions">
                @unless (auth()->user()?->isViewOnly())
                    <button type="button" id="btn-fetch-live" onclick="fetchLiveData()"
                            class="flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-green-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        Fetch Data
                    </button>
                @endunless
                <flux:button variant="filled" icon="arrow-down-tray" :href="route('live-streaming.export')">
                    Export to Excel
                </flux:button>
                <flux:modal.trigger name="modal-create-live-stream">
                    <flux:button variant="primary" icon="plus">Tambah Live Streaming</flux:button>
                </flux:modal.trigger>
            </div>
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

        <div class="admin-table-shell">
            <form method="GET" action="{{ route('live-streaming.index') }}" id="filter-form">
                <x-admin.table-toolbar :filters="$filters" :show-filters="true" search-placeholder="Cari platform atau additional info...">
                    <flux:input name="date_from" type="date" value="{{ $filters['date_from'] }}" class="w-40" aria-label="{{ __('Dari tanggal') }}" />
                    <flux:input name="date_to" type="date" value="{{ $filters['date_to'] }}" class="w-40" aria-label="{{ __('Sampai tanggal') }}" />

                    <select name="platform" onchange="this.form.submit()" class="admin-filter-select">
                        <option value="">{{ __('Semua Platform') }}</option>
                        <option value="Showroom" {{ $filters['platform'] === 'Showroom' ? 'selected' : '' }}>Showroom</option>
                        <option value="IDN App" {{ $filters['platform'] === 'IDN App' ? 'selected' : '' }}>IDN App</option>
                    </select>

                    <select name="sort_by" onchange="this.form.submit()" class="admin-filter-select">
                        <option value="live_date" {{ $filters['sort_by'] === 'live_date' ? 'selected' : '' }}>Live Date</option>
                        <option value="platform" {{ $filters['sort_by'] === 'platform' ? 'selected' : '' }}>Platform</option>
                        <option value="duration" {{ $filters['sort_by'] === 'duration' ? 'selected' : '' }}>Duration</option>
                    </select>

                    <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] }}" id="sort-dir-input" />
                    <flux:tooltip content="{{ $filters['sort_dir'] === 'asc' ? 'Ascending' : 'Descending' }}">
                        <button
                            type="button"
                            onclick="document.getElementById('sort-dir-input').value = '{{ $filters['sort_dir'] === 'asc' ? 'desc' : 'asc' }}'; document.getElementById('filter-form').submit();"
                            class="admin-filter-sort"
                        >
                            @if ($filters['sort_dir'] === 'asc')
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>
                            @endif
                        </button>
                    </flux:tooltip>

                    <flux:button type="submit" variant="outline">{{ __('Terapkan') }}</flux:button>

                    @if ($filters['search'] || $filters['platform'] || $filters['date_from'] || $filters['date_to'])
                        <a href="{{ route('live-streaming.index') }}" class="admin-filter-reset">{{ __('Reset') }}</a>
                    @endif
                </x-admin.table-toolbar>
            </form>

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
                                    <div class="flex items-center justify-end gap-3">
                                        <button
                                            type="button"
                                            class="admin-action-link"
                                            data-detail="{{ json_encode([
                                                'platform' => $liveStream->platform,
                                                'live_date' => $liveStream->live_date?->translatedFormat('d F Y'),
                                                'start_time' => $liveStream->start_time?->translatedFormat('d F Y, H:i'),
                                                'end_time' => $liveStream->end_time?->translatedFormat('d F Y, H:i'),
                                                'duration' => $liveStream->duration,
                                                'max_viewers' => $liveStream->max_viewers,
                                                'comment_count' => $liveStream->comment_count,
                                                'gift_count' => $liveStream->gift_count,
                                                'total_gold' => $liveStream->total_gold,
                                                'gifts' => $liveStream->gifts ?? [],
                                                'top_senders' => $liveStream->top_senders ?? [],
                                            ]) }}"
                                            onclick="openDetailLiveStreamModal(this)"
                                        >
                                            Detail
                                        </button>
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
                                    </div>
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
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Simpan</flux:button>
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
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Update</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="modal-detail-live-stream" class="max-w-6xl">
        <div id="detail-capture-area" class="space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Detail Live Streaming</flux:heading>
                    <flux:subheading id="detail-subtitle">–</flux:subheading>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Kolom kiri: info sesi --}}
                <div class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Informasi Sesi</p>

                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Platform</dt>
                            <dd id="detail-platform" class="font-semibold text-zinc-800 dark:text-zinc-200">–</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Start Time</dt>
                            <dd id="detail-start" class="font-semibold text-zinc-800 dark:text-zinc-200">–</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">End Time</dt>
                            <dd id="detail-end" class="font-semibold text-zinc-800 dark:text-zinc-200">–</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Duration</dt>
                            <dd id="detail-duration" class="font-semibold text-zinc-800 dark:text-zinc-200">–</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Jumlah Penonton</dt>
                            <dd id="detail-viewers" class="font-semibold text-zinc-800 dark:text-zinc-200">–</dd>
                        </div>
                    </dl>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-amber-50 p-3 text-center dark:bg-amber-950/30">
                            <p id="detail-gift-count" class="text-2xl font-black text-amber-600 dark:text-amber-400">0</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Gift</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 p-3 text-center dark:bg-emerald-950/30">
                            <p id="detail-total-gold" class="text-2xl font-black text-emerald-600 dark:text-emerald-400">0</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Total Gold</p>
                        </div>
                        <div class="col-span-2 rounded-lg bg-sky-50 p-3 text-center dark:bg-sky-950/30">
                            <p id="detail-comment-count" class="text-2xl font-black text-sky-600 dark:text-sky-400">0</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Komentar</p>
                        </div>
                    </div>
                </div>

                {{-- Kolom tengah: gift & komentar --}}
                <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div>
                        <p class="mb-2 text-sm font-semibold text-zinc-700 dark:text-zinc-200">Gift List</p>
                        <div id="detail-gifts" class="grid max-h-72 grid-cols-2 gap-2 overflow-y-auto pr-1"></div>
                    </div>
                </div>

                {{-- Kolom kanan: top gifter --}}
                <div class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Top Gifter</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div id="detail-senders-left" class="space-y-2"></div>
                        <div id="detail-senders-right" class="space-y-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-end gap-3">
            <button
                type="button"
                id="btn-capture-detail"
                onclick="captureDetailLiveStreamModal(this)"
                class="inline-flex items-center gap-2 rounded-xl bg-zinc-800 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                </svg>
                Capture
            </button>
        </div>
    </flux:modal>

    <script src="https://cdn.jsdelivr.net/npm/html2canvas-pro@1.5.11/dist/html2canvas-pro.min.js"></script>
    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';

        function fetchLiveData() {
            const btn = document.getElementById('btn-fetch-live');
            if (!btn) return;

            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.textContent = 'Fetching...';

            fetch('{{ route('live-streaming.fetch-manually') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                window.appAlert(data.message || (data.success ? 'Data berhasil di-fetch!' : 'Terjadi kesalahan saat fetch data.'), { variant: data.success ? 'success' : 'error' })
                    .then(() => { if (data.success) location.reload(); });
            })
            .catch(() => window.appAlert('Terjadi kesalahan saat fetch data.', { variant: 'error' }))
            .finally(() => { btn.disabled = false; btn.innerHTML = originalContent; });
        }

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

        function formatDuration(minutes) {
            if (minutes === null || minutes === undefined || minutes === '' || isNaN(minutes)) {
                return '–';
            }

            const total = Number(minutes);
            const h = Math.floor(total / 60);
            const m = total % 60;

            return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
        }

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';

            return div.innerHTML;
        }

        function openDetailLiveStreamModal(target) {
            let data = {};

            try {
                data = JSON.parse(target.dataset.detail || '{}');
            } catch (error) {
                data = {};
            }

            document.getElementById('detail-subtitle').textContent =
                `${data.platform || '–'} • ${data.live_date || '–'}`;
            document.getElementById('detail-platform').textContent = data.platform || '–';
            document.getElementById('detail-start').textContent = data.start_time || '–';
            document.getElementById('detail-end').textContent = data.end_time || '–';
            document.getElementById('detail-duration').textContent = formatDuration(data.duration);
            document.getElementById('detail-viewers').textContent = Number(data.max_viewers || 0).toLocaleString('id-ID');
            document.getElementById('detail-gift-count').textContent = Number(data.gift_count || 0).toLocaleString('id-ID');
            document.getElementById('detail-total-gold').textContent = Number(data.total_gold || 0).toLocaleString('id-ID');
            document.getElementById('detail-comment-count').textContent = Number(data.comment_count || 0).toLocaleString('id-ID');

            const giftsEl = document.getElementById('detail-gifts');
            const gifts = Array.isArray(data.gifts) ? data.gifts : [];

            giftsEl.innerHTML = gifts.length === 0
                ? '<p class="col-span-2 py-6 text-center text-xs text-zinc-400">Tidak ada gift.</p>'
                : gifts.map((gift) => `
                    <div class="flex items-center gap-2 rounded-lg border border-zinc-100 bg-zinc-50 p-2 dark:border-zinc-800 dark:bg-zinc-800/50">
                        ${gift.image_url
                            ? `<img src="${escapeHtml(gift.image_url)}" alt="${escapeHtml(gift.name)}" class="size-8 shrink-0 rounded object-contain" loading="lazy">`
                            : '<span class="flex size-8 shrink-0 items-center justify-center rounded bg-zinc-200 text-xs dark:bg-zinc-700">?</span>'}
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-semibold text-zinc-800 dark:text-zinc-200">${escapeHtml(gift.name)}</p>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">${Number(gift.gold_per_unit || 0).toLocaleString('id-ID')} gold × ${Number(gift.count || 0).toLocaleString('id-ID')}</p>
                            <p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400">${Number(gift.total_gold || 0).toLocaleString('id-ID')} gold</p>
                        </div>
                    </div>
                `).join('');

            const senderColumns = [
                document.getElementById('detail-senders-left'),
                document.getElementById('detail-senders-right'),
            ];

            senderColumns.forEach((column) => { column.innerHTML = ''; });

            const senders = Array.isArray(data.top_senders) ? data.top_senders : [];
            const topSenders = senders.slice(0, 10);

            if (topSenders.length === 0) {
                senderColumns[0].innerHTML = '<p class="col-span-2 py-6 text-center text-xs text-zinc-400">Tidak ada data.</p>';
            } else {
                topSenders.forEach((sender, index) => {
                    const card = document.createElement('div');
                    card.className = 'flex items-center gap-2 rounded-lg border border-zinc-100 bg-zinc-50 p-2 dark:border-zinc-800 dark:bg-zinc-800/50';
                    card.innerHTML = `
                        ${sender.avatar
                            ? `<img src="${escapeHtml(sender.avatar)}" alt="${escapeHtml(sender.name)}" class="size-8 shrink-0 rounded-full object-cover" loading="lazy">`
                            : '<span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs dark:bg-zinc-700">?</span>'}
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-semibold text-zinc-800 dark:text-zinc-200">${escapeHtml(sender.name)}</p>
                            <p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400">${Number(sender.total_gold || 0).toLocaleString('id-ID')} gold</p>
                        </div>
                    `;
                    senderColumns[index < 5 ? 0 : 1].appendChild(card);
                });
            }

            Flux.modal('modal-detail-live-stream').show();
        }

        async function captureDetailLiveStreamModal(button) {
            const area = document.getElementById('detail-capture-area');
            const originalButtonContent = button.innerHTML;
            button.disabled = true;
            button.textContent = 'Capturing...';

            const isDark = document.documentElement.classList.contains('dark');
            const captureEdge = 24;
            const previousPadding = area.style.padding;
            const previousBoxSizing = area.style.boxSizing;

            try {
                if (typeof html2canvas !== 'function') {
                    throw new Error('Capture library is unavailable.');
                }

                area.style.padding = `${captureEdge}px`;
                area.style.boxSizing = 'content-box';

                const capturedCanvas = await html2canvas(area, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: isDark ? '#18181b' : '#ffffff',
                    logging: false,
                });

                const link = document.createElement('a');
                const now = new Date();
                const ts = now.getFullYear()
                    + String(now.getMonth() + 1).padStart(2, '0')
                    + String(now.getDate()).padStart(2, '0') + '-'
                    + String(now.getHours()).padStart(2, '0')
                    + String(now.getMinutes()).padStart(2, '0')
                    + String(now.getSeconds()).padStart(2, '0');
                link.download = `live-streaming-detail-${ts}.png`;
                link.href = capturedCanvas.toDataURL('image/png');
                link.click();
            } catch (error) {
                console.error('Capture failed:', error);
                alert('Gagal meng-capture detail live streaming.\n\nDetail: ' + (error && error.message ? error.message : String(error)));
            } finally {
                area.style.padding = previousPadding;
                area.style.boxSizing = previousBoxSizing;
                button.disabled = false;
                button.innerHTML = originalButtonContent;
            }
        }
    </script>
</x-layouts::app>
