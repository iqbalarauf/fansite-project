@php
    $background = match ($block['data']['background'] ?? 'white') {
        'soft' => 'bg-zinc-100',
        'accent' => 'bg-indigo-600 text-white',
        default => 'bg-white',
    };
    $padding = match ($block['data']['padding'] ?? 'medium') {
        'small' => 'p-4',
        'large' => 'p-10',
        default => 'p-6',
    };
@endphp

@switch($block['type'])
    @case('container')
        <div class="rounded-xl {{ $background }} {{ $padding }} text-center text-sm font-semibold">{{ __('Container') }}</div>
        @break
    @case('text')
        <p class="whitespace-pre-line leading-7 text-zinc-600 dark:text-zinc-300">{{ $block['data']['text'] ?? '' }}</p>
        @break
    @case('image')
        @if (! empty($block['data']['url']))
            <img src="{{ $block['data']['url'] }}" alt="{{ $block['data']['alt'] ?? '' }}" class="max-h-64 w-full rounded-xl object-cover">
        @else
            <div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-sm text-zinc-500">{{ __('Tambahkan URL gambar') }}</div>
        @endif
        @break
    @case('video')
        @if (! empty($block['data']['url']))
            <video controls class="w-full rounded-xl" aria-label="{{ $block['data']['title'] ?? '' }}"><source src="{{ $block['data']['url'] }}"></video>
        @else
            <div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-sm text-zinc-500">{{ __('Tambahkan URL video') }}</div>
        @endif
        @break
    @case('button')
        <span class="inline-flex rounded-full bg-indigo-600 px-5 py-2.5 font-bold text-white">{{ $block['data']['label'] ?? '' }}</span>
        @break
@endswitch
