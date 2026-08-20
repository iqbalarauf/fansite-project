@switch($block['type'] ?? '')
    @case('container')
        @php
            $background = match ($block['data']['background'] ?? 'white') {
                'soft' => 'bg-zinc-200',
                'accent' => 'bg-indigo-600 text-white',
                default => 'bg-white',
            };
            $padding = match ($block['data']['padding'] ?? 'medium') {
                'small' => 'p-4',
                'large' => 'p-10',
                default => 'p-6',
            };
            $columns = $block['data']['columns'] ?? [['blocks' => []]];
        @endphp
        <section class="grid gap-5 {{ count($columns) === 2 ? 'md:grid-cols-2' : 'grid-cols-1' }} rounded-2xl {{ $background }} {{ $padding }}">
            @foreach ($columns as $column)
                <div class="space-y-5">
                    @foreach ($column['blocks'] ?? [] as $block)
                        @include('custom-pages.render-block', ['block' => $block])
                    @endforeach
                </div>
            @endforeach
        </section>
        @break
    @case('text')
        <p class="whitespace-pre-line text-lg leading-8 text-slate-700">{{ $block['data']['text'] ?? '' }}</p>
        @break
    @case('image')
        <img src="{{ $block['data']['url'] ?? '' }}" alt="{{ $block['data']['alt'] ?? '' }}" class="max-h-[560px] w-full rounded-2xl object-cover shadow-sm">
        @break
    @case('video')
        @php
            preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([^?&/]+)~', $block['data']['url'] ?? '', $youtubeMatch);
        @endphp
        @if (! empty($youtubeMatch[1]))
            <iframe src="https://www.youtube.com/embed/{{ $youtubeMatch[1] }}" title="{{ $block['data']['title'] ?? '' }}" class="aspect-video w-full rounded-2xl shadow-sm" allowfullscreen></iframe>
        @endif
        @break
    @case('button')
        <a href="{{ $block['data']['url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex rounded-full bg-indigo-600 px-6 py-3 font-bold text-white transition hover:bg-indigo-700">{{ $block['data']['label'] ?? '' }}</a>
        @break
    @case('embed')
        {!! $block['data']['html'] ?? '' !!}
        @break
@endswitch
