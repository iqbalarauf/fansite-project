<x-layouts::app :title="__('Rekaman Live')">
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">Rekaman Live</flux:heading>
                <flux:subheading>Arsip rekaman live oshimen yang dibuat otomatis dari /api/now_live</flux:subheading>
            </div>
            <div class="admin-page-actions">
                <span class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                    Retensi {{ $retentionDays }} hari
                </span>
            </div>
        </div>

        @if (! $recordingEnabled)
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
                Perekaman otomatis sedang <strong>nonaktif</strong>. Set <code>LIVE_RECORDING_ENABLED=true</code> pada <code>.env</code> server untuk mengaktifkannya.
            </div>
        @endif

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="admin-table-shell">
            <form method="GET" action="{{ route('live-recordings.index') }}" id="filter-form">
                <x-admin.table-toolbar :filters="$filters" :show-filters="true" search-placeholder="Cari nama member, slug, atau url key...">
                    <flux:input name="date_from" type="date" value="{{ $filters['date_from'] }}" class="w-40" aria-label="{{ __('Dari tanggal') }}" />
                    <flux:input name="date_to" type="date" value="{{ $filters['date_to'] }}" class="w-40" aria-label="{{ __('Sampai tanggal') }}" />

                    <select name="platform" onchange="this.form.submit()" class="admin-filter-select">
                        <option value="">{{ __('Semua Platform') }}</option>
                        <option value="Showroom" {{ $filters['platform'] === 'Showroom' ? 'selected' : '' }}>Showroom</option>
                        <option value="IDN App" {{ $filters['platform'] === 'IDN App' ? 'selected' : '' }}>IDN App</option>
                    </select>

                    <select name="status" onchange="this.form.submit()" class="admin-filter-select">
                        <option value="">{{ __('Semua Status') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" {{ $filters['status'] === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                        @endforeach
                    </select>

                    <select name="sort_by" onchange="this.form.submit()" class="admin-filter-select">
                        <option value="started_at" {{ $filters['sort_by'] === 'started_at' ? 'selected' : '' }}>Waktu Mulai</option>
                        <option value="member_name" {{ $filters['sort_by'] === 'member_name' ? 'selected' : '' }}>Nama Member</option>
                        <option value="platform" {{ $filters['sort_by'] === 'platform' ? 'selected' : '' }}>Platform</option>
                        <option value="duration_seconds" {{ $filters['sort_by'] === 'duration_seconds' ? 'selected' : '' }}>Durasi</option>
                        <option value="file_size" {{ $filters['sort_by'] === 'file_size' ? 'selected' : '' }}>Ukuran File</option>
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

                    @if ($filters['search'] || $filters['platform'] || $filters['status'] || $filters['date_from'] || $filters['date_to'])
                        <a href="{{ route('live-recordings.index') }}" class="admin-filter-reset">{{ __('Reset') }}</a>
                    @endif
                </x-admin.table-toolbar>
            </form>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">MEMBER</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">PLATFORM</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">MULAI</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">DURASI</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">UKURAN</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">STATUS</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-500 dark:text-zinc-400">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @forelse ($recordings as $recording)
                            @php
                                $startedAt = \App\Support\Timezone::toLocal($recording->started_at);
                                $subtitle = ($recording->platform ?: '–').' • '.($startedAt?->translatedFormat('d F Y, H:i') ?? '–');
                            @endphp
                            <tr class="admin-table-row">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $recording->member_name ?: '–' }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $recording->live_slug }}</div>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $recording->platform ?: '–' }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $startedAt?->translatedFormat('d M Y, H:i') ?? '–' }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $recording->durationLabel() }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $recording->fileSizeLabel() }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $recording->status->badgeClasses() }}"
                                        @if ($recording->error_message) title="{{ $recording->error_message }}" @endif
                                    >
                                        {{ $recording->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        @if ($recording->status->isPlayable())
                                            <button
                                                type="button"
                                                class="admin-action-link"
                                                data-preview-url="{{ route('live-recordings.preview', $recording) }}"
                                                data-download-url="{{ route('live-recordings.download', $recording) }}"
                                                data-title="{{ $recording->member_name ?: 'Rekaman Live' }}"
                                                data-subtitle="{{ $subtitle }}"
                                                onclick="openRecordingPreview(this)"
                                            >
                                                Preview
                                            </button>
                                            <a href="{{ route('live-recordings.download', $recording) }}" class="admin-action-link">
                                                Download
                                            </a>
                                        @else
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">Belum tersedia</span>
                                        @endif

                                        @unless (auth()->user()?->isViewOnly())
                                            <form
                                                method="POST"
                                                action="{{ route('live-recordings.destroy', $recording) }}"
                                                onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus rekaman '.$recording->live_slug.'? File video juga akan dihapus permanen.') }});"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-action-link text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                        <p class="font-medium">Belum ada rekaman live</p>
                                        <p class="text-xs">Rekaman akan muncul di sini setelah live oshimen terdeteksi.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('show-teater.partials.pagination', ['paginator' => $recordings, 'perPage' => $filters['per_page'], 'pageParam' => 'page'])
        </div>
    </div>

    <flux:modal name="modal-preview-recording" class="max-w-5xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg" id="preview-recording-title">Preview Rekaman</flux:heading>
                <flux:subheading id="preview-recording-subtitle">–</flux:subheading>
            </div>

            <video id="preview-recording-video" class="w-full rounded-xl bg-black" controls playsinline preload="metadata"></video>

            <div class="flex items-center justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost" onclick="closeRecordingPreview()">Tutup</flux:button>
                </flux:modal.close>
                <flux:button id="preview-recording-download" variant="primary" icon="arrow-down-tray" href="#">
                    Download
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <script>
        function openRecordingPreview(button) {
            const video = document.getElementById('preview-recording-video');

            video.src = button.dataset.previewUrl || '';
            document.getElementById('preview-recording-title').textContent = button.dataset.title || 'Preview Rekaman';
            document.getElementById('preview-recording-subtitle').textContent = button.dataset.subtitle || '';
            document.getElementById('preview-recording-download').href = button.dataset.downloadUrl || '#';

            Flux.modal('modal-preview-recording').show();
        }

        function closeRecordingPreview() {
            const video = document.getElementById('preview-recording-video');

            video.pause();
            video.removeAttribute('src');
            video.load();
        }
    </script>
</x-layouts::app>
