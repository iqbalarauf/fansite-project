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
    @case('gallery')
        @php
            $galleryImages = array_values(array_filter(array_map(function ($image) {
                $path = $image['storage_path'] ?? null;

                if (blank($path)) {
                    return null;
                }

                return [
                    'url' => Storage::url($path),
                    'alt' => (string) ($image['alt'] ?? ''),
                ];
            }, $data['images'] ?? [])));

            $galleryTotal = count($galleryImages);
            $visibleCount = max(1, min((int) ($data['initial_count'] ?? 3), max(1, $galleryTotal)));
            $hasCarousel = $galleryTotal > $visibleCount;
            $imageRounded = $preview ? 'rounded-lg' : 'rounded-xl';
            $imageHeight = $preview ? 'h-28' : 'h-48';
            $galleryItemWidth = 'calc((100% - '.($visibleCount - 1).'rem) / '.$visibleCount.')';
        @endphp
        @if ($galleryTotal === 0)
            @if ($preview)
                <div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-sm text-zinc-500">{{ __('Tambahkan gambar gallery') }}</div>
            @endif
        @else
            <div class="relative px-1" data-page-gallery data-page-gallery-visible="{{ $visibleCount }}">
                <div class="overflow-hidden">
                    <div class="flex gap-4 transition-transform duration-500 ease-out" data-page-gallery-track>
                        @foreach ($galleryImages as $image)
                            <div data-page-gallery-item class="shrink-0 overflow-hidden {{ $imageRounded }}" style="width: {{ $galleryItemWidth }};">
                                <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="w-full {{ $imageHeight }} object-cover" loading="lazy" decoding="async">
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($hasCarousel)
                    <button type="button" data-page-gallery-nav data-page-gallery-prev aria-label="{{ __('Previous') }}"
                            class="absolute -left-2 top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-zinc-200 bg-white/90 text-zinc-600 shadow-md backdrop-blur transition hover:text-indigo-600 disabled:opacity-40 dark:border-zinc-700 dark:bg-zinc-900/90 dark:text-zinc-300 dark:hover:text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd"/></svg>
                    </button>
                    <button type="button" data-page-gallery-nav data-page-gallery-next aria-label="{{ __('Next') }}"
                            class="absolute -right-2 top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-zinc-200 bg-white/90 text-zinc-600 shadow-md backdrop-blur transition hover:text-indigo-600 disabled:opacity-40 dark:border-zinc-700 dark:bg-zinc-900/90 dark:text-zinc-300 dark:hover:text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                    </button>
                @endif
            </div>
        @endif
        @break
    @case('youtube_playlist')
        @php
            $playlistUrl = (string) ($data['playlist_url'] ?? '');
            $playlistVideos = filled($playlistUrl) ? \App\Support\YoutubePlaylist::videos($playlistUrl, 7) : [];
            $playlistVisible = max(1, min((int) ($data['visible_count'] ?? 3), 4));
            $playlistHeading = trim((string) ($data['section_title'] ?? '')) ?: __('Lihat konten terbaru');
            $playlistHasCarousel = count($playlistVideos) > $playlistVisible;
            $cardRounded = $preview ? 'rounded-xl' : 'rounded-2xl';
        @endphp
        @if ($playlistVideos === [])
            @if ($preview)
                <div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-sm text-zinc-500">{{ __('Tambahkan URL playlist YouTube') }}</div>
            @endif
        @else
            <div class="space-y-4">
                @if (filled($playlistHeading))
                    <p class="text-xl font-black text-zinc-900 dark:text-zinc-100">{{ $playlistHeading }}</p>
                @endif

                <div class="relative px-1" data-page-youtube data-page-youtube-visible="{{ $playlistVisible }}">
                    <div class="overflow-hidden">
                        <div class="flex gap-4 transition-transform duration-500 ease-out" data-page-youtube-track>
                            @foreach ($playlistVideos as $video)
                                <a
                                    href="{{ $video['url'] }}"
                                    target="_blank"
                                    rel="noopener"
                                    data-page-youtube-item
                                    class="group flex w-full shrink-0 flex-col overflow-hidden {{ $cardRounded }} border border-zinc-200 bg-white shadow-sm transition hover:border-indigo-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-indigo-700"
                                    style="width: calc((100% - {{ ($playlistVisible - 1) }}rem) / {{ $playlistVisible }});"
                                >
                                    <div class="aspect-video w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                        @if ($video['thumbnail'])
                                            <img src="{{ $video['thumbnail'] }}" alt="{{ $video['title'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async">
                                        @endif
                                    </div>
                                    <div class="flex flex-1 flex-col gap-2 p-4">
                                        <p class="line-clamp-2 text-sm font-bold text-zinc-900 transition group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $video['title'] }}</p>
                                        @if (! empty($video['description']))
                                            <p class="line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">{{ $video['description'] }}</p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if ($playlistHasCarousel)
                        <button type="button" data-page-youtube-nav data-page-youtube-prev aria-label="{{ __('Previous') }}"
                                class="absolute -left-2 top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-zinc-200 bg-white/90 text-zinc-600 shadow-md backdrop-blur transition hover:text-indigo-600 disabled:opacity-40 dark:border-zinc-700 dark:bg-zinc-900/90 dark:text-zinc-300 dark:hover:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd"/></svg>
                        </button>
                        <button type="button" data-page-youtube-nav data-page-youtube-next aria-label="{{ __('Next') }}"
                                class="absolute -right-2 top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-zinc-200 bg-white/90 text-zinc-600 shadow-md backdrop-blur transition hover:text-indigo-600 disabled:opacity-40 dark:border-zinc-700 dark:bg-zinc-900/90 dark:text-zinc-300 dark:hover:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                        </button>
                    @endif
                </div>
            </div>
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
