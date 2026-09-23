@extends('layouts.public', ['title' => null, 'active' => 'home'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
        use Illuminate\Support\Carbon;

        $heroUrl = $heroImage ? Storage::url($heroImage) : null;
        $heroDisplay = in_array($heroImageDisplay ?? 'fit', ['fit', 'contain', 'adjustable', 'original'], true) ? ($heroImageDisplay ?? 'fit') : 'fit';
        $heroAdjustable = $heroUrl !== null && $heroDisplay === 'adjustable';
        $heroBackgroundClass = match ($heroDisplay) {
            'contain' => 'bg-contain bg-center bg-no-repeat',
            'original' => 'bg-auto bg-center bg-no-repeat',
            default => 'bg-cover bg-center',
        };
        $heroStyle = ($heroUrl !== null && ! $heroAdjustable) ? "background-image: url('".$heroUrl."');" : '';

        $liveIcons = collect([
            'showroom' => 'icon-app/showroom.webp',
            'idn' => 'icon-app/idn.webp',
        ])->map(fn (string $path): ?string => Storage::disk('public')->exists($path) ? Storage::url($path) : null)->all();

        $heroSwapData = [
            ['text' => $heroName1Text, 'color' => $heroName1Color],
            ['text' => $heroName2Text, 'color' => $heroName2Color],
        ];
    @endphp

    <section id="home"
             class="relative overflow-hidden bg-slate-950 {{ $heroAdjustable ? '' : 'flex min-h-svh items-center' }} {{ ($heroUrl !== null && ! $heroAdjustable) ? $heroBackgroundClass : '' }}"
             style="{{ $heroStyle }}">
        @if ($heroAdjustable)
            <img src="{{ $heroUrl }}" alt="" class="block h-auto w-full" loading="eager" fetchpriority="high" decoding="async" />
            <div class="absolute inset-0 bg-slate-950/50"></div>
        @else
            <div class="absolute inset-0"></div>
        @endif

        <div class="{{ $heroAdjustable ? 'absolute inset-0 flex items-center' : 'relative z-10 w-full' }}">
            <div class="mx-auto flex w-full max-w-7xl flex-col items-start px-4 py-24 text-left sm:px-6 lg:px-8 lg:py-28">
                <h1 class="text-4xl font-black leading-tight sm:text-5xl lg:text-6xl" style="color: {{ $welcomeTitleColor }}">
                    <span class="inline-block">{{ $welcomeTitle }}</span>
                    @if ($heroNameAnimate && $idolProfileVersion === 'jkt48' && filled($idolShortname) && $heroName2Text !== '' && $heroName2Text !== $heroName1Text)
                        <span
                            class="mt-2 block"
                            style="color: {{ $heroName1Color }}"
                            data-hero-swap='@json($heroSwapData)'
                            data-hero-swap-interval="4000"
                        >{{ $heroName1Text }}</span>
                    @else
                        <span class="mt-2 block" style="color: {{ $heroName1Color }}">{{ $heroName1Text }}</span>
                    @endif
                </h1>
                <p class="mt-5 max-w-xl text-base text-indigo-100 sm:text-lg">Temukan aktivitas terbaru, jadwal, dan momen favorit dari {{ $idolName }} dalam satu halaman yang selalu diperbarui.</p>

                @if (! empty($heroButtons))
                    <div class="mt-8 flex flex-wrap justify-start gap-4">
                        @foreach ($heroButtons as $index => $button)
                            <a href="{{ $button['url'] }}" class="{{ $index === 0
                                ? 'rounded-full bg-yellow-300 px-6 py-3 text-sm font-bold text-slate-900 shadow-lg shadow-yellow-200/50 transition hover:bg-yellow-200'
                                : 'rounded-full border border-white/40 bg-white/10 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/15' }}">{{ $button['label'] }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        @if ($showOnWelcome || $feedEnabled)
                <aside id="about" class="flex flex-col gap-8">
                    @if ($showOnWelcome)
                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                            <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">About</p>
                            <h3 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Tentang {{ $idolName }}</h3>

                            <div class="mt-6">
                                @if ($idolPhoto)
                                    <img src="{{ Storage::url($idolPhoto) }}" alt="{{ $idolName }}" class="h-[380px] w-full rounded-[1.5rem] object-cover shadow-lg shadow-indigo-200/50 dark:shadow-none" loading="lazy" decoding="async" />
                                @else
                                    <div class="flex h-[380px] w-full items-center justify-center rounded-[1.5rem] border border-slate-200 bg-slate-100 text-xl font-bold text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">{{ $idolName }}</div>
                                @endif
                            </div>

                            <x-social-media-icons :instagram="$idolInstagramUrl" :twitter="$idolTwitterUrl" :tiktok="$idolTiktokUrl" class="mt-6 justify-center" />

                            <p class="mt-6 text-base leading-8 text-slate-600 dark:text-slate-300">{{ $idolDescription }}</p>

                            <a href="{{ route('about.show', $idolSlug ?: null) }}"
                               class="mt-6 flex w-full items-center justify-center rounded-full bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
                                Berkenalan dengan {{ $idolShortname ?: $idolName }}
                            </a>
                        </div>
                    @endif

                    @if ($feedEnabled)
                        <div class="flex flex-1 flex-col rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                            <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5 dark:border-slate-800">
                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">{{ $feed['label'] }}</p>
                                    <h3 class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $feed['heading'] }}</h3>
                                </div>
                                <a href="{{ $feed['indexRoute'] }}" class="shrink-0 text-sm font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">Lihat Semua</a>
                            </div>

                            <div class="mt-6 flex-1 space-y-4">
                                @forelse ($feed['items'] as $item)
                                    <a href="{{ $item['url'] }}" class="group flex items-start gap-4">
                                        @if ($item['cover'])
                                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                                <img src="{{ Storage::url($item['cover']) }}" alt="{{ $item['title'] }}" class="h-full w-full object-cover" loading="lazy" decoding="async" />
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="line-clamp-2 text-sm font-bold text-slate-900 transition group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $item['title'] }}</p>
                                            @if ($item['date'])
                                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ Carbon::parse($item['date'])->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada konten terbaru.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </aside>
            @endif

        <div class="flex flex-col gap-8">
                <section id="data" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5 dark:border-slate-800">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Statistik</p>
                            <h3 class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Data Oniel</h3>
                        </div>
                        <div class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300">
                            Last Event: {{ $lastEventDate ? Carbon::parse($lastEventDate)->locale('id')->isoFormat('D MMMM YYYY') : '-' }}
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-slate-100 p-5 dark:bg-slate-800/60">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Show Teater</p>
                            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ max(0, $showCount - $upcomingShowCount) }}</p>
                            @if ($upcomingShowCount > 0)
                                <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-900 dark:text-green-300">
                                    <span class="size-1.5 rounded-full bg-green-500 dark:bg-green-400"></span>
                                    {{ $upcomingShowCount }} Upcoming Show
                                </span>
                            @endif
                        </div>
                        <div class="rounded-2xl bg-indigo-50 p-5 dark:bg-indigo-950/60">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Single Participation</p>
                            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white" data-test="single-participation-count">{{ $discographyCount }}</p>
                        </div>
                        <div class="rounded-2xl bg-yellow-50 p-5 dark:bg-yellow-950/50">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Jumlah Setlist</p>
                            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $setlistCount }}</p>
                        </div>
                    </div>
                </section>

                <section id="schedule" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5 dark:border-slate-800">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Schedule</p>
                            <h3 class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Event Mendatang</h3>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($upcomingEvents as $event)
                            @php
                                $daysUntil = (int) now()->startOfDay()->diffInDays(Carbon::parse($event['date'])->startOfDay(), false);
                                $badgeColors = [
                                    'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
                                    'red' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
                                    'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
                                ];
                            @endphp
                            <div class="flex items-center gap-4 rounded-2xl border border-green-100 bg-green-50 p-4 dark:border-green-900/40 dark:bg-green-950/20">
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeColors[$event['badge_color']] }}">
                                    {{ $event['type'] }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    @if (! empty($event['purchase_link']))
                                        <a href="{{ $event['purchase_link'] }}" target="_blank" rel="noopener" class="line-clamp-2 text-base font-bold text-slate-900 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">{{ $event['name'] }}</a>
                                    @else
                                        <p class="line-clamp-2 text-base font-bold text-slate-900 dark:text-white">{{ $event['name'] }}</p>
                                    @endif
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ Carbon::parse($event['date'])->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                                </div>
                                <span class="shrink-0 text-xs font-medium text-green-600 dark:text-green-400">
                                    {{ $daysUntil === 0 ? __('Hari Ini') : 'H-'.$daysUntil }}
                                </span>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">Tidak ada event mendatang yang terjadwal.</div>
                        @endforelse
                    </div>
                </section>

                <section id="live" class="flex flex-1 flex-col rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                    <div class="border-b border-slate-200 pb-5 dark:border-slate-800">
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Live</p>
                        <h3 class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Status Live</h3>
                    </div>

                    @php
                        $livePlatforms = [
                            ['key' => 'showroom', 'label' => 'Showroom Live', 'live' => $showroomLive, 'url' => $showroomStreamUrl, 'badge' => 'SR', 'accent' => 'from-orange-400 to-rose-500'],
                            ['key' => 'idn', 'label' => 'IDN App', 'live' => $idnLive, 'url' => $idnStreamUrl, 'badge' => 'IDN', 'accent' => 'from-sky-400 to-emerald-500'],
                        ];
                    @endphp

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach ($livePlatforms as $platform)
                            @php
                                $isLink = $platform['live'] && ! empty($platform['url']);
                                $icon = $liveIcons[$platform['key']] ?? null;
                            @endphp
                            <div class="relative flex flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-5 transition dark:border-slate-800 dark:bg-slate-800/40 {{ $isLink ? 'hover:border-indigo-300 hover:shadow-md dark:hover:border-indigo-700' : '' }}">
                                @if ($icon)
                                    <img src="{{ $icon }}" alt="{{ $platform['label'] }}" class="size-14 rounded-2xl object-contain shadow-md" />
                                @else
                                    <div class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br {{ $platform['accent'] }} text-base font-black text-white shadow-md">
                                        {{ $platform['badge'] }}
                                    </div>
                                @endif

                                <p class="text-base font-bold text-slate-900 dark:text-white">{{ $platform['label'] }}</p>

                                @if ($platform['live'])
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900 dark:text-green-300">
                                        <span class="size-1.5 rounded-full bg-green-500 dark:bg-green-400"></span>
                                        Online
                                    </span>
                                    @if ($isLink)
                                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Tonton sekarang') }} →</span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-200/70 px-3 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                        <span class="size-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                        Offline
                                    </span>
                                @endif

                                @if ($isLink)
                                    <a href="{{ $platform['url'] }}" target="_blank" rel="noopener" class="absolute inset-0 rounded-2xl" aria-label="{{ __('Tonton :platform', ['platform' => $platform['label']]) }}"></a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>


        </div>

        @if ($galleryPhotos->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Galeri</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Foto Terbaru</h2>
                </div>
                <a href="{{ route('gallery.index') }}" class="shrink-0 text-sm font-semibold text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">Lihat Semua</a>
            </div>

            <div class="relative mt-6 px-1" data-gallery-carousel>
                <div class="overflow-hidden">
                    <div class="flex gap-4 transition-transform duration-500 ease-out" data-gallery-track>
                        @foreach ($galleryPhotos as $photo)
                            <a href="{{ route('gallery.index') }}" data-gallery-item class="group w-[calc((100%-2rem)/3)] shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <img src="{{ Storage::url($photo->photo) }}" alt="{{ $photo->description ?: 'Gallery photo' }}" class="aspect-square w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" />
                            </a>
                        @endforeach
                    </div>
                </div>

                <button type="button" data-gallery-nav data-gallery-prev aria-label="Foto sebelumnya"
                        class="absolute -left-2 top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-600 shadow-md backdrop-blur transition hover:text-indigo-600 disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-300 dark:hover:text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd"/></svg>
                </button>
                <button type="button" data-gallery-nav data-gallery-next aria-label="Foto berikutnya"
                        class="absolute -right-2 top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-600 shadow-md backdrop-blur transition hover:text-indigo-600 disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-300 dark:hover:text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </section>
    @endif
    </section>

    @php
        $youtubeHasContent = $youtubeEnabled && ($youtubeDisplayMode === 'embed'
            ? filled($youtubeEmbedUrl)
            : count($youtubeVideos) > 0);
    @endphp

    @if ($youtubeHasContent)
        <section id="playlist" class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Playlist</p>
                        <h2 class="mt-2 text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Lihat konten terbaru</h2>
                    </div>

                    @if (filled($youtubePlaylistUrl))
                        <a href="{{ $youtubePlaylistUrl }}" target="_blank" rel="noopener"
                           class="inline-flex shrink-0 items-center gap-2 rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
                            {{ __('Lihat di YouTube') }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path d="M11 3a1 1 0 1 0 0 2h2.586l-6.293 6.293a1 1 0 1 0 1.414 1.414L15 6.414V9a1 1 0 1 0 2 0V4a1 1 0 0 0-1-1h-5Z"/><path d="M5 5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-3a1 1 0 1 0-2 0v3H5V7h3a1 1 0 0 0 0-2H5Z"/></svg>
                        </a>
                    @endif
                </div>

                @if ($youtubeDisplayMode === 'embed')
                    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
                        <div class="relative aspect-video">
                            <iframe
                                src="{{ $youtubeEmbedUrl }}"
                                title="Youtube Playlist"
                                class="absolute inset-0 h-full w-full"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                                loading="lazy"
                            ></iframe>
                        </div>
                    </div>
                @else
                    <div class="relative mt-6 px-1" data-youtube-carousel>
                        <div class="overflow-hidden">
                            <div class="flex gap-4 transition-transform duration-500 ease-out" data-youtube-track>
                                @foreach ($youtubeVideos as $video)
                                    <a href="{{ $video['url'] }}" target="_blank" rel="noopener" data-youtube-item
                                       class="group flex w-[calc((100%-2rem)/3)] shrink-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-indigo-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-700">
                                        <div class="aspect-video w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                                            @if ($video['thumbnail'])
                                                <img src="{{ $video['thumbnail'] }}" alt="{{ $video['title'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" />
                                            @endif
                                        </div>
                                        <div class="flex flex-1 flex-col gap-2 p-4">
                                            <p class="line-clamp-2 text-sm font-bold text-slate-900 transition group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $video['title'] }}</p>
                                            @if ($video['description'])
                                                <p class="line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ $video['description'] }}</p>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        @if (count($youtubeVideos) > 3)
                            <button type="button" data-youtube-nav data-youtube-prev aria-label="Video sebelumnya"
                                    class="absolute -left-2 top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-600 shadow-md backdrop-blur transition hover:text-indigo-600 disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-300 dark:hover:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd"/></svg>
                            </button>
                            <button type="button" data-youtube-nav data-youtube-next aria-label="Video berikutnya"
                                    class="absolute -right-2 top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-600 shadow-md backdrop-blur transition hover:text-indigo-600 disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-300 dark:hover:text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </section>
    @endif

    <script>
        (function () {
            function setupCarousel(root, selectors, visible) {
                var track = root.querySelector(selectors.track);
                var items = Array.prototype.slice.call(root.querySelectorAll(selectors.item));
                var prev = root.querySelector(selectors.prev);
                var next = root.querySelector(selectors.next);
                var navs = Array.prototype.slice.call(root.querySelectorAll(selectors.nav));
                var index = 0;
                var maxIndex = Math.max(0, items.length - visible);

                if (!track || items.length === 0) {
                    return;
                }

                if (items.length <= visible) {
                    navs.forEach(function (nav) { nav.classList.add('hidden'); });
                    return;
                }

                function step() {
                    var styles = window.getComputedStyle(track);
                    var gap = parseFloat(styles.columnGap || styles.gap) || 0;

                    return items[0].getBoundingClientRect().width + gap;
                }

                function update() {
                    track.style.transform = 'translateX(' + (-index * step()) + 'px)';

                    if (prev) prev.disabled = index <= 0;
                    if (next) next.disabled = index >= maxIndex;
                }

                if (prev) prev.addEventListener('click', function () { if (index > 0) { index--; update(); } });
                if (next) next.addEventListener('click', function () { if (index < maxIndex) { index++; update(); } });
                window.addEventListener('resize', update);
                update();
            }

            document.querySelectorAll('[data-gallery-carousel]').forEach(function (root) {
                setupCarousel(root, {
                    track: '[data-gallery-track]',
                    item: '[data-gallery-item]',
                    prev: '[data-gallery-prev]',
                    next: '[data-gallery-next]',
                    nav: '[data-gallery-nav]',
                }, 3);
            });

            document.querySelectorAll('[data-youtube-carousel]').forEach(function (root) {
                setupCarousel(root, {
                    track: '[data-youtube-track]',
                    item: '[data-youtube-item]',
                    prev: '[data-youtube-prev]',
                    next: '[data-youtube-next]',
                    nav: '[data-youtube-nav]',
                }, 3);
            });

            document.querySelectorAll('[data-hero-swap]').forEach(function (el) {
                var items = [];

                try {
                    items = JSON.parse(el.getAttribute('data-hero-swap')) || [];
                } catch (error) {
                    return;
                }

                if (!Array.isArray(items) || items.length < 2) {
                    return;
                }

                var applyItem = function (item) {
                    if (item && typeof item === 'object') {
                        el.textContent = item.text || '';

                        if (item.color) {
                            el.style.color = item.color;
                        }

                        return;
                    }

                    el.textContent = item || '';
                };

                var index = 0;
                var interval = parseInt(el.getAttribute('data-hero-swap-interval') || '4000', 10);

                setInterval(function () {
                    index = (index + 1) % items.length;
                    el.classList.remove('hero-swap-animate');
                    void el.offsetWidth;
                    applyItem(items[index]);
                    el.classList.add('hero-swap-animate');
                }, interval);
            });
        })();
    </script>
@endsection
