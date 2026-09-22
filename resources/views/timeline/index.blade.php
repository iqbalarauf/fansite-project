@extends('layouts.public', ['title' => 'Timeline', 'active' => 'timeline'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <section class="bg-gradient-to-br from-indigo-950 via-slate-950 to-violet-950">
        <div class="mx-auto flex max-w-7xl flex-col items-start px-4 py-20 text-left sm:px-6 lg:px-8 lg:py-24">
            <span class="mb-4 inline-flex w-fit rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-50">Timeline</span>
            <h1 class="max-w-2xl text-4xl font-black leading-tight text-white sm:text-5xl">Timeline</h1>
            <p class="mt-4 max-w-xl text-base text-indigo-100 sm:text-lg">Perjalanan dan momen penting.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($timelines->isEmpty())
            <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-base font-semibold text-slate-700 dark:text-slate-200">Belum ada data timeline.</p>
            </div>
        @else
            <ol class="relative mx-auto max-w-4xl" data-timeline>
                <span aria-hidden="true" class="absolute left-4 top-0 h-full w-0.5 -translate-x-1/2 bg-gradient-to-b from-indigo-500 via-indigo-400/60 to-transparent dark:from-indigo-500 dark:via-indigo-700 md:left-1/2"></span>

                @foreach ($timelines as $index => $timeline)
                    @php $isLeft = $index % 2 === 0; @endphp
                    <li data-timeline-item class="relative mb-8 translate-y-8 pl-12 opacity-0 transition-all duration-700 ease-out md:mb-12 md:grid md:grid-cols-2 md:gap-10 md:pl-0">
                        <span class="absolute left-4 top-3 z-10 size-3.5 -translate-x-1/2 rounded-full bg-indigo-500 ring-4 ring-white dark:ring-slate-950 md:left-1/2" aria-hidden="true"></span>

                        <div class="{{ $isLeft ? 'md:col-start-1 md:pr-10 md:text-right' : 'md:col-start-2 md:pl-10' }}">
                            <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">
                                {{ $timeline->date?->locale('id')->isoFormat('D MMMM YYYY') }}
                            </span>

                            <article class="mt-3 inline-block w-full overflow-hidden rounded-2xl border border-slate-200 bg-white text-left shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                @if ($timeline->image)
                                    <img src="{{ Storage::url($timeline->image) }}" alt="Timeline {{ $timeline->id }}" class="max-h-80 w-full object-cover" loading="lazy" decoding="async" />
                                @endif
                                @if ($timeline->description)
                                    <p class="p-5 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $timeline->description }}</p>
                                @endif
                            </article>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    @if ($timelines->isNotEmpty())
        <script>
            (function () {
                var items = document.querySelectorAll('[data-timeline-item]');
                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                function reveal(el) {
                    el.classList.remove('opacity-0', 'translate-y-8');
                    el.classList.add('opacity-100', 'translate-y-0');
                }

                if (reduce || !('IntersectionObserver' in window)) {
                    items.forEach(reveal);

                    return;
                }

                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            reveal(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

                items.forEach(function (item) { observer.observe(item); });
            })();
        </script>
    @endif
@endsection
