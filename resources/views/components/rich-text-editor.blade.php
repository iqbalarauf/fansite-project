@props(['name', 'value' => '', 'placeholder' => 'Tulis konten di sini...'])

<div data-rich-text class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
    <div class="flex flex-wrap items-center gap-1 border-b border-zinc-200 bg-zinc-50 p-2 dark:border-zinc-700 dark:bg-zinc-800/50">
        @foreach ([
            'bold' => 'B',
            'italic' => 'I',
            'underline' => 'U',
            'strike' => 'S',
            'h2' => 'H2',
            'h3' => 'H3',
            'bulletList' => 'Daftar',
            'orderedList' => '1. Daftar',
            'blockquote' => 'Kutipan',
            'codeBlock' => 'Kode',
            'link' => 'Tautan',
            'image' => 'Gambar',
            'undo' => 'Undo',
            'redo' => 'Redo',
            'clear' => 'Bersihkan',
        ] as $command => $label)
            <button type="button" data-rich-text-command="{{ $command }}" class="rich-text-toolbar-btn">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div data-rich-text-canvas class="rich-content min-h-64 max-w-none px-4 py-3 focus:outline-none"></div>

    <textarea data-rich-text-input name="{{ $name }}" data-placeholder="{{ $placeholder }}" class="hidden">{{ $value }}</textarea>
</div>
