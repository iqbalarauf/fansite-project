@extends('layouts.public', ['title' => 'Tentang '.($fanbaseName ?? 'Fansite'), 'active' => 'fansite'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;

        $historyIsCustom = ($fanbaseHistoryEnabled ?? false)
            && ($fanbaseHistorySource ?? 'default') === 'custom'
            && ! empty($fanbaseHistoryCustomPage);
        $historyItems = $fanbaseHistoryItems ?? [];
        $historyIsModal = ($fanbaseHistoryEnabled ?? false) && ! $historyIsCustom && count($historyItems) > 0;
        $historyButton = $historyIsCustom || $historyIsModal;
    @endphp

    <section class="bg-slate-950">
        <div class="bg-gradient-to-br from-indigo-950/85 via-slate-950/70 to-violet-950/80">
            <div class="mx-auto flex max-w-3xl flex-col items-center px-4 py-24 text-center sm:px-6 lg:py-28">
                <span class="mb-4 inline-flex w-fit rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-50">About Fansite</span>
                @if ($fanbaseLogo)
                    <img src="{{ Storage::url($fanbaseLogo) }}" alt="{{ $fanbaseName }}" class="mb-6 h-20 w-20 rounded-2xl object-cover shadow-lg ring-4 ring-white/10" loading="eager" decoding="async" />
                @endif
                <h1 class="text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">{{ $fanbaseName }}</h1>
                @if ($fanbaseDescription)
                    <p class="mt-5 max-w-xl text-base text-indigo-100 sm:text-lg">{{ $fanbaseDescription }}</p>
                @endif
            </div>
        </div>
    </section>

    @if (($fanbaseActivitiesEnabled ?? true) && (count($fanbaseActivities) > 0 || $historyButton))
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white">Kegiatan Fanbase</h2>

                    @if ($historyButton)
                        @if ($historyIsCustom)
                            <a href="{{ route('custom-pages.show', $fanbaseHistoryCustomPage['slug']) }}" class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">Sejarah Kami</a>
                        @else
                            <button type="button" data-history-open class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">Sejarah Kami</button>
                        @endif
                    @endif
                </div>

                @if (count($fanbaseActivities) > 0)
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @foreach ($fanbaseActivities as $activity)
                            <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                                <span class="mt-1 flex size-6 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-black text-white">{{ $loop->iteration }}</span>
                                <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $activity }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if (($fanbaseStructureEnabled ?? true) && count($fanbaseStructure) > 0)
        <section class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <h2 class="text-3xl font-black text-slate-900 dark:text-white">Struktur Organisasi</h2>
                <ul class="mt-6 space-y-3">
                    @foreach ($fanbaseStructure as $line)
                        <li class="flex items-start gap-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                            <span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                            <span>{{ $line }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if (count($fanbaseGalleryItems) > 0)
        <section class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-black text-slate-900 dark:text-white">Galeri</h2>

            <div class="relative mt-8 px-1" data-fanbase-carousel>
                <div class="overflow-hidden">
                    <div class="flex gap-4 transition-transform duration-500 ease-out" data-fanbase-track>
                        @foreach ($fanbaseGalleryItems as $index => $item)
                            <figure data-fanbase-item class="fanbase-gallery-item flex shrink-0 flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <img src="{{ Storage::url($item['photo']) }}" alt="{{ $item['caption'] ?: 'Galeri '.$fanbaseName.' '.($index + 1) }}" class="aspect-square w-full object-cover" loading="lazy" decoding="async" />
                                @if ($item['caption'])
                                    <figcaption class="p-4 text-sm text-slate-600 dark:text-slate-300">{{ $item['caption'] }}</figcaption>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                </div>

                @if (count($fanbaseGalleryItems) > 1)
                    <button type="button" data-fanbase-prev aria-label="Foto sebelumnya"
                            class="absolute -left-2 top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-600 shadow-md backdrop-blur transition hover:text-indigo-600 disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-300 dark:hover:text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd"/></svg>
                    </button>
                    <button type="button" data-fanbase-next aria-label="Foto berikutnya"
                            class="absolute -right-2 top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-600 shadow-md backdrop-blur transition hover:text-indigo-600 disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-300 dark:hover:text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                    </button>
                @endif
            </div>
        </section>
    @endif

    @if ($fanbaseCtaEnabled && $fanbaseCtaTitle)
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[2rem] bg-slate-950 bg-cover bg-center px-6 py-20 text-center sm:px-12"
                 style="{{ $fanbaseCtaBackground ? "background-image: url('".Storage::url($fanbaseCtaBackground)."');" : '' }}">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-950/90 via-slate-950/80 to-violet-950/85"></div>
                <div class="relative z-10 mx-auto max-w-2xl">
                    <h2 class="text-3xl font-black leading-tight text-white sm:text-4xl">{{ $fanbaseCtaTitle }}</h2>
                    <div class="mt-8 flex flex-wrap justify-center gap-4">
                        @if ($fanbaseCtaButton1Text)
                            <a href="{{ $fanbaseCtaButton1Link }}" target="_blank" rel="noopener" class="rounded-full bg-yellow-300 px-6 py-3 text-sm font-bold text-slate-900 shadow-lg shadow-yellow-200/40 transition hover:bg-yellow-200">{{ $fanbaseCtaButton1Text }}</a>
                        @endif
                        @if ($fanbaseCtaButton2Text)
                            <a href="{{ $fanbaseCtaButton2Link }}" target="_blank" rel="noopener" class="rounded-full border border-white/40 bg-white/10 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/15">{{ $fanbaseCtaButton2Text }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($historyIsModal)
        <div id="fanbase-history-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="fanbase-history-title">
            <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" data-history-close></div>

            <div class="relative z-10 flex max-h-full w-full max-w-4xl flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 id="fanbase-history-title" class="text-xl font-black text-slate-900 dark:text-white">Sejarah {{ $fanbaseName }}</h2>
                    <button type="button" data-history-close aria-label="Tutup" class="flex size-9 shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:text-slate-900 dark:border-slate-700 dark:text-slate-400 dark:hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-6 overflow-y-auto overscroll-contain px-6 py-6">
                    @foreach ($historyItems as $item)
                        @if ($item['photo'])
                            <article class="grid gap-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-800/50">
                                <img src="{{ Storage::url($item['photo']) }}" alt="Sejarah {{ $fanbaseName }} {{ $loop->iteration }}" class="h-full w-full object-cover" loading="lazy" decoding="async" />
                                <div class="flex items-center p-5">
                                    <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item['description'] }}</p>
                                </div>
                            </article>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <script>
        (function () {
            document.querySelectorAll('[data-fanbase-carousel]').forEach(function (root) {
                var track = root.querySelector('[data-fanbase-track]');
                var items = Array.prototype.slice.call(root.querySelectorAll('[data-fanbase-item]'));
                var prev = root.querySelector('[data-fanbase-prev]');
                var next = root.querySelector('[data-fanbase-next]');

                if (!track || items.length === 0) {
                    return;
                }

                var index = 0;

                function metrics() {
                    var styles = window.getComputedStyle(track);
                    var gap = parseFloat(styles.columnGap || styles.gap) || 0;
                    var itemWidth = items[0].getBoundingClientRect().width;
                    var viewportWidth = track.parentElement.getBoundingClientRect().width;
                    var visible = Math.max(1, Math.floor((viewportWidth + gap) / (itemWidth + gap)));

                    return {
                        step: itemWidth + gap,
                        maxIndex: Math.max(0, items.length - visible),
                    };
                }

                function update() {
                    var m = metrics();

                    if (index > m.maxIndex) {
                        index = m.maxIndex;
                    }

                    track.style.transform = 'translateX(' + (-index * m.step) + 'px)';

                    if (prev) prev.disabled = index <= 0;
                    if (next) next.disabled = index >= m.maxIndex;
                }

                if (prev) prev.addEventListener('click', function () { if (index > 0) { index--; update(); } });
                if (next) next.addEventListener('click', function () {
                    if (index < metrics().maxIndex) { index++; update(); }
                });

                window.addEventListener('resize', update);
                update();
            });
        })();

        @if ($historyIsModal)
            (function () {
                var modal = document.getElementById('fanbase-history-modal');

                if (!modal) {
                    return;
                }

                function openModal() {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = '';
                }

                document.querySelectorAll('[data-history-open]').forEach(function (trigger) {
                    trigger.addEventListener('click', openModal);
                });

                modal.querySelectorAll('[data-history-close]').forEach(function (trigger) {
                    trigger.addEventListener('click', closeModal);
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                        closeModal();
                    }
                });
            })();
        @endif
    </script>
@endsection