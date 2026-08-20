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
        @php
            $columns = $block['data']['columns'] ?? [['blocks' => []]];
        @endphp
        <div class="grid gap-4 {{ count($columns) === 2 ? 'md:grid-cols-2' : 'grid-cols-1' }} rounded-xl {{ $background }} {{ $padding }}">
            @foreach ($columns as $column)
                <div class="min-h-20 space-y-3 rounded-lg border border-dashed border-current/20 p-3">
                    @forelse ($column['blocks'] ?? [] as $childBlock)
                        @include('custom-pages.block-preview', ['block' => $childBlock])
                    @empty
                        <div class="flex min-h-12 items-center justify-center text-xs text-current/50">{{ __('Empty column') }}</div>
                    @endforelse
                </div>
            @endforeach
        </div>
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
        @php
            preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([^?&/]+)~', $block['data']['url'] ?? '', $youtubeMatch);
        @endphp
        @if (! empty($youtubeMatch[1]))
            <iframe src="https://www.youtube.com/embed/{{ $youtubeMatch[1] }}" title="{{ $block['data']['title'] ?? '' }}" class="aspect-video w-full rounded-xl" allowfullscreen></iframe>
        @else
            <div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-sm text-zinc-500">{{ __('Tambahkan link YouTube') }}</div>
        @endif
        @break
    @case('button')
        <span class="inline-flex rounded-full bg-indigo-600 px-5 py-2.5 font-bold text-white">{{ $block['data']['label'] ?? '' }}</span>
        @break
    @case('embed')
        <div class="overflow-hidden rounded-xl border border-dashed border-zinc-300 p-3">{!! $block['data']['html'] ?? '' !!}</div>
        @break
@endswitch
