@props(['block', 'preview' => false])

@php
    use App\Support\CustomPageStatistic;
    use App\Support\HtmlSanitizer;
    use Illuminate\Support\Facades\Storage;

    $data = $block['data'] ?? [];

    $backgroundValue = $data['background'] ?? 'white';
    $hasInlineBackground = (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $backgroundValue);
    $background = $hasInlineBackground ? '' : match ($backgroundValue) {
        'soft' => 'bg-zinc-100',
        'accent' => 'bg-indigo-600 text-white',
        default => 'bg-white',
    };
    $padding = match ($data['padding'] ?? 'medium') {
        'small' => 'p-4',
        'large' => 'p-10',
        default => 'p-6',
    };
    $verticalAlignment = match ($data['vertical_alignment'] ?? 'top') {
        'middle' => 'items-center',
        'bottom' => 'items-end',
        default => 'items-start',
    };
    $textAlignment = match ($data['alignment'] ?? 'left') {
        'center' => 'text-center',
        'right' => 'text-right',
        'justify' => 'text-justify',
        default => 'text-left',
    };
    $textColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'] ?? '') ? $data['color'] : '#2E2F3E';
    $imageSrc = ! empty($data['storage_path'] ?? null) ? Storage::url($data['storage_path']) : ($data['url'] ?? '');
@endphp

@switch($block['type'] ?? '')
    @case('container')
        @php
            $columns = $data['columns'] ?? [['blocks' => []]];
            $gridClass = count($columns) === 2 ? 'md:grid-cols-2' : 'grid-cols-1';
        @endphp
        @if ($preview)
            <div class="grid gap-4 {{ $gridClass }} {{ $verticalAlignment }} rounded-xl {{ $background }} {{ $padding }}" @if ($hasInlineBackground) style="background-color: {{ $backgroundValue }}" @endif>
                @foreach ($columns as $column)
                    <div class="min-h-20 space-y-3 rounded-lg border border-dashed border-current/20 p-3">
                        @forelse ($column['blocks'] ?? [] as $childBlock)
                            <x-custom-page-block :block="$childBlock" preview />
                        @empty
                            <div class="flex min-h-12 items-center justify-center text-xs text-current/50">{{ __('Empty column') }}</div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        @else
            <section class="grid gap-5 {{ $gridClass }} {{ $verticalAlignment }} rounded-2xl {{ $background }} {{ $padding }}" @if ($hasInlineBackground) style="background-color: {{ $backgroundValue }}" @endif>
                @foreach ($columns as $column)
                    <div class="space-y-5">
                        @foreach ($column['blocks'] ?? [] as $childBlock)
                            <x-custom-page-block :block="$childBlock" />
                        @endforeach
                    </div>
                @endforeach
            </section>
        @endif
        @break
    @case('text')
        <p class="whitespace-pre-line {{ $preview ? 'leading-7' : 'text-lg leading-8' }} {{ $textAlignment }} {{ ($data['bold'] ?? false) ? 'font-bold' : '' }} {{ ($data['italic'] ?? false) ? 'italic' : '' }} {{ ($data['underline'] ?? false) ? 'underline' : '' }}" style="color: {{ $textColor }}">{{ $data['text'] ?? '' }}</p>
        @break
    @case('statistic')
        @if ($preview)
            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-indigo-950">
                <div class="text-sm font-semibold">{{ $data['label'] ?? __('Statistic') }}</div>
                <div class="mt-1 text-3xl font-bold">{{ number_format(CustomPageStatistic::value($data)) }}</div>
            </div>
        @else
            <section class="rounded-2xl border border-indigo-100 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">{{ $data['label'] ?? __('Statistic') }}</p>
                <p class="mt-2 text-4xl font-black text-indigo-700">{{ number_format(CustomPageStatistic::value($data)) }}</p>
            </section>
        @endif
        @break
    @case('image')
        @if ($preview)
            @if (! empty($imageSrc))
                <img src="{{ $imageSrc }}" alt="{{ $data['alt'] ?? '' }}" class="max-h-64 w-full rounded-xl object-cover">
            @else
                <div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-sm text-zinc-500">{{ __('Tambahkan URL gambar atau upload') }}</div>
            @endif
        @else
            <img src="{{ $imageSrc }}" alt="{{ $data['alt'] ?? '' }}" class="max-h-[560px] w-full rounded-2xl object-cover shadow-sm" loading="lazy" decoding="async">
        @endif
        @break
    @case('video')
        @php
            preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([^?&/]+)~', $data['url'] ?? '', $youtubeMatch);
        @endphp
        @if (! empty($youtubeMatch[1]))
            <iframe src="https://www.youtube.com/embed/{{ $youtubeMatch[1] }}" title="{{ $data['title'] ?? '' }}" class="{{ $preview ? 'aspect-video w-full rounded-xl' : 'aspect-video w-full rounded-2xl shadow-sm' }}" allowfullscreen></iframe>
        @elseif ($preview)
            <div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-sm text-zinc-500">{{ __('Tambahkan link YouTube') }}</div>
        @endif
        @break
    @case('button')
        @if ($preview)
            <span class="inline-flex rounded-full bg-indigo-600 px-5 py-2.5 font-bold text-white">{{ $data['label'] ?? '' }}</span>
        @else
            <a href="{{ $data['url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex rounded-full bg-indigo-600 px-6 py-3 font-bold text-white transition hover:bg-indigo-700">{{ $data['label'] ?? '' }}</a>
        @endif
        @break
    @case('embed')
        @if ($preview)
            <div class="overflow-hidden rounded-xl border border-dashed border-zinc-300 p-3">{!! HtmlSanitizer::embed($data['html'] ?? '') !!}</div>
        @else
            {!! HtmlSanitizer::embed($data['html'] ?? '') !!}
        @endif
        @break
@endswitch
