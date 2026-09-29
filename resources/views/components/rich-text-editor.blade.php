@props(['name', 'value' => '', 'placeholder' => 'Tulis konten di sini...', 'statistics' => false])

@php
    $tools = [
        ['command' => 'bold', 'icon' => 'bold', 'label' => 'Tebal'],
        ['command' => 'italic', 'icon' => 'italic', 'label' => 'Miring'],
        ['command' => 'underline', 'icon' => 'underline', 'label' => 'Garis Bawah'],
        ['command' => 'strike', 'icon' => 'strikethrough', 'label' => 'Coret'],
        ['command' => 'h2', 'icon' => 'hashtag', 'label' => 'Heading 2', 'badge' => '2'],
        ['command' => 'h3', 'icon' => 'hashtag', 'label' => 'Heading 3', 'badge' => '3'],
        ['command' => 'bulletList', 'icon' => 'list-bullet', 'label' => 'Daftar Poin'],
        ['command' => 'orderedList', 'icon' => 'numbered-list', 'label' => 'Daftar Nomor'],
        ['command' => 'blockquote', 'icon' => 'chat-bubble-bottom-center-text', 'label' => 'Kutipan'],
        ['command' => 'codeBlock', 'icon' => 'code-bracket', 'label' => 'Blok Kode'],
        ['command' => 'link', 'icon' => 'link', 'label' => 'Tautan'],
        ['command' => 'image', 'icon' => 'photo', 'label' => 'Gambar'],
        ['command' => 'undo', 'icon' => 'arrow-uturn-left', 'label' => 'Undo'],
        ['command' => 'redo', 'icon' => 'arrow-uturn-right', 'label' => 'Redo'],
        ['command' => 'clear', 'icon' => 'trash', 'label' => 'Bersihkan Format'],
    ];

    if ($statistics) {
        $tools[] = ['command' => 'statisticCard', 'icon' => 'chart-bar', 'label' => 'Statistic Card'];
    }

    $statMetricGroups = [
        'show' => [
            'label' => 'Show Teater',
            'metrics' => [
                ['value' => 'show_teater_all', 'label' => 'Count all'],
                ['value' => 'show_teater_date_range', 'label' => 'Count by range date', 'date' => true],
                ['value' => 'show_teater_setlist', 'label' => 'Count by setlist', 'setlist' => true],
            ],
        ],
        'unit' => [
            'label' => 'Unit Song',
            'metrics' => [
                ['value' => 'unit_song_all', 'label' => 'Count all'],
                ['value' => 'unit_song_date_range', 'label' => 'Count by range date', 'date' => true],
                ['value' => 'unit_song_setlist', 'label' => 'Count by setlist', 'setlist' => true],
            ],
        ],
        'center' => [
            'label' => 'Center US',
            'metrics' => [
                ['value' => 'center_unit_song_all', 'label' => 'Count all'],
                ['value' => 'center_unit_song_unit_song', 'label' => 'Count by unit song', 'unit_song' => true],
                ['value' => 'center_unit_song_setlist', 'label' => 'Count by setlist', 'setlist' => true],
                ['value' => 'center_unit_song_date_range', 'label' => 'Count by range date', 'date' => true],
            ],
        ],
        'global' => [
            'label' => 'Global Center',
            'metrics' => [
                ['value' => 'global_center_date_range', 'label' => 'Count by range date', 'date' => true],
                ['value' => 'global_center_setlist', 'label' => 'Count by setlist', 'setlist' => true],
            ],
        ],
    ];

    $statSetlists = $statistics
        ? DB::table('show_teater')->whereNull('deleted_at')->whereNotNull('setlist')->where('setlist', '!=', '')->distinct()->orderBy('setlist')->pluck('setlist')->all()
        : [];
    $statUnitSongs = $statistics
        ? DB::table('show_teater')->whereNull('deleted_at')->whereNotNull('unit_song')->where('unit_song', '!=', '')->distinct()->orderBy('unit_song')->pluck('unit_song')->all()
        : [];
@endphp

<div
    data-rich-text
    data-image-upload-url="{{ route('content.editor.image') }}"
    data-csrf-token="{{ csrf_token() }}"
    @if ($statistics && \Illuminate\Support\Facades\Route::has('content.editor.statistic'))
        data-statistic-upload-url="{{ route('content.editor.statistic') }}"
        data-stat-setlists="{{ json_encode($statSetlists) }}"
        data-stat-unit-songs="{{ json_encode($statUnitSongs) }}"
    @endif
    class="overflow-hidden admin-card"
>
    <div class="flex flex-wrap items-center gap-1 border-b border-zinc-200 bg-zinc-50 p-2 dark:border-zinc-700 dark:bg-zinc-800/50">
        @foreach ($tools as $tool)
            <flux:tooltip content="{{ $tool['label'] }}">
                <button
                    type="button"
                    data-rich-text-command="{{ $tool['command'] }}"
                    aria-label="{{ $tool['label'] }}"
                    class="rich-text-toolbar-btn inline-flex size-8 items-center justify-center p-0"
                >
                    <span class="relative inline-flex">
                        <flux:icon :icon="$tool['icon']" variant="mini" />
                        @if (! empty($tool['badge']))
                            <span class="absolute -right-1.5 -top-1 text-[9px] font-bold leading-none">{{ $tool['badge'] }}</span>
                        @endif
                    </span>
                </button>
            </flux:tooltip>
        @endforeach
    </div>

    <input type="file" accept="image/*" data-rich-text-image-input class="hidden">

    <div data-rich-text-canvas class="rich-content min-h-64 max-w-none px-4 py-3 focus:outline-none"></div>

    <textarea data-rich-text-input name="{{ $name }}" data-placeholder="{{ $placeholder }}" class="hidden">{{ $value }}</textarea>

    @if ($statistics)
        <div data-stat-dialog class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                <flux:heading size="lg">Statistic Card</flux:heading>
                <flux:subheading>Sisipkan kartu statistik. Nilai dihitung saat ini dan tidak ikut berubah otomatis.</flux:subheading>

                <div class="mt-5 space-y-4">
                    <div>
                        <flux:label for="stat-metric">Data source</flux:label>
                        <select id="stat-metric" data-stat-field="metric" class="admin-filter-select mt-1 w-full">
                            @foreach ($statMetricGroups as $group)
                                <optgroup label="{{ $group['label'] }}">
                                    @foreach ($group['metrics'] as $metric)
                                        <option value="{{ $metric['value'] }}">{{ $metric['label'] }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <flux:label for="stat-label">Label</flux:label>
                        <flux:input id="stat-label" data-stat-field="label" class="mt-1" />
                    </div>

                    <div data-stat-dates class="hidden grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:label for="stat-date-from">Start date</flux:label>
                            <flux:input id="stat-date-from" data-stat-field="date_from" type="date" class="mt-1" />
                        </div>
                        <div>
                            <flux:label for="stat-date-to">End date</flux:label>
                            <flux:input id="stat-date-to" data-stat-field="date_to" type="date" class="mt-1" />
                        </div>
                    </div>

                    <div data-stat-setlist class="hidden">
                        <flux:label for="stat-setlist">Setlist</flux:label>
                        <select id="stat-setlist" data-stat-field="setlist" class="admin-filter-select mt-1 w-full">
                            <option value="">Select setlist</option>
                            @foreach ($statSetlists as $setlist)
                                <option value="{{ $setlist }}">{{ $setlist }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div data-stat-unit-song class="hidden">
                        <flux:label for="stat-unit-song">Unit song</flux:label>
                        <select id="stat-unit-song" data-stat-field="unit_song" class="admin-filter-select mt-1 w-full">
                            <option value="">Select unit song</option>
                            @foreach ($statUnitSongs as $unitSong)
                                <option value="{{ $unitSong }}">{{ $unitSong }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Preview</p>
                        <p data-stat-preview class="mt-1 text-3xl font-black text-indigo-700 dark:text-indigo-400">—</p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <flux:button type="button" data-stat-cancel variant="ghost">Batal</flux:button>
                    <flux:button type="button" data-stat-insert variant="primary">Sisipkan</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
