@props(['name', 'value' => '', 'placeholder' => 'Tulis konten di sini...'])

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
@endphp

<div data-rich-text data-image-upload-url="{{ route('content.editor.image') }}" data-csrf-token="{{ csrf_token() }}" class="overflow-hidden admin-card">
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
</div>
