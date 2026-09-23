@props(['block', 'preview' => false])

@php
    use App\Support\CustomPageStatistic;
    use App\Support\HtmlSanitizer;
    use Illuminate\Support\Facades\Storage;

    $data = $block['data'] ?? [];

    $backgroundValue = $data['background'] ?? 'white';
    $hasInlineBackground = (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $backgroundValue);
    $background = $hasInlineBackground ? '' : match ($backgroundValue) {
        'transparent' => '',
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
    $fontSizeClass = match ($data['font_size'] ?? 'default') {
        'sm' => 'text-sm leading-6',
        'base' => 'text-base leading-7',
        'lg' => 'text-lg leading-8',
        'xl' => 'text-xl leading-8',
        '2xl' => 'text-2xl leading-9',
        '3xl' => 'text-3xl leading-10',
        '4xl' => 'text-4xl leading-tight',
        default => $preview ? 'leading-7' : 'text-lg leading-8',
    };
    $headingTag = in_array($data['heading'] ?? 'none', ['h1', 'h2', 'h3', 'h4'], true) ? $data['heading'] : 'p';

    $buttonAlignment = match ($data['alignment'] ?? 'left') {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
    $buttonBg = preg_match('/^#[0-9A-Fa-f]{6}$/', $data['bg_color'] ?? '') ? $data['bg_color'] : '#4F46E5';
    $buttonText = preg_match('/^#[0-9A-Fa-f]{6}$/', $data['text_color'] ?? '') ? $data['text_color'] : '#FFFFFF';

    $imageSource = $data['source'] ?? ((! empty($data['storage_path'] ?? null)) ? 'upload' : 'url');
    $imageSrc = $imageSource === 'url'
        ? ($data['url'] ?? '')
        : (! empty($data['storage_path'] ?? null) ? Storage::url($data['storage_path']) : ($data['url'] ?? ''));
    $imageDisplay = in_array($data['display'] ?? 'fit', ['fit', 'contain', 'auto', 'original'], true) ? ($data['display'] ?? 'fit') : 'fit';
    $imageRounded = $preview ? 'rounded-xl' : 'rounded-2xl shadow-sm';
    $imageMaxHeight = $preview ? 'max-h-64' : 'max-h-[560px]';
    $imageClass = match ($imageDisplay) {
        'contain' => "w-full {$imageMaxHeight} object-contain {$imageRounded}",
        'auto' => "h-auto w-full {$imageRounded}",
        'original' => "max-w-none {$imageRounded}",
        default => "w-full {$imageMaxHeight} object-cover {$imageRounded}",
    };
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
        <{{ $headingTag }} class="whitespace-pre-line {{ $fontSizeClass }} {{ $textAlignment }} {{ ($data['bold'] ?? false) || $headingTag !== 'p' ? 'font-bold' : '' }} {{ ($data['italic'] ?? false) ? 'italic' : '' }} {{ ($data['underline'] ?? false) ? 'underline' : '' }}" style="color: {{ $textColor }}">{{ $data['text'] ?? '' }}</{{ $headingTag }}>
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
                <img src="{{ $imageSrc }}" alt="{{ $data['alt'] ?? '' }}" class="{{ $imageClass }}">
            @else
                <div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-sm text-zinc-500">{{ __('Tambahkan URL gambar atau upload') }}</div>
            @endif
        @else
            <img src="{{ $imageSrc }}" alt="{{ $data['alt'] ?? '' }}" class="{{ $imageClass }}" loading="lazy" decoding="async">
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
        <div class="{{ $buttonAlignment }}">
            @if ($preview)
                <span class="inline-flex rounded-full px-5 py-2.5 font-bold" style="background-color: {{ $buttonBg }}; color: {{ $buttonText }}">{{ $data['label'] ?? '' }}</span>
            @else
                <a href="{{ $data['url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex rounded-full px-6 py-3 font-bold transition hover:opacity-90" style="background-color: {{ $buttonBg }}; color: {{ $buttonText }}">{{ $data['label'] ?? '' }}</a>
            @endif
        </div>
        @break
    @case('embed')
        @if ($preview)
            <div class="overflow-hidden rounded-xl border border-dashed border-zinc-300 p-3">{!! HtmlSanitizer::embed($data['html'] ?? '') !!}</div>
        @else
            {!! HtmlSanitizer::embed($data['html'] ?? '') !!}
        @endif
        @break
@endswitch
