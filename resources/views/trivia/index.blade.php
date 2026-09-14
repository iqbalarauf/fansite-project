@extends('layouts.public', ['title' => 'Trivia', 'active' => 'trivia'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <section class="bg-gradient-to-br from-indigo-950 via-slate-950 to-violet-950">
        <div class="mx-auto flex max-w-7xl flex-col items-start px-4 py-20 text-left sm:px-6 lg:px-8 lg:py-24">
            <span class="mb-4 inline-flex w-fit rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-50">Trivia</span>
            <h1 class="max-w-2xl text-4xl font-black leading-tight text-white sm:text-5xl">Trivia</h1>
            <p class="mt-4 max-w-xl text-base text-indigo-100 sm:text-lg">Kumpulan trivia menarik. Cari dengan kata kunci favoritmu.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('trivia.index') }}">
            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-0 flex-1">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Cari trivia..."
                           class="w-full rounded-full border border-slate-200 bg-white px-5 py-3 text-sm text-slate-700 shadow-sm outline-hidden transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                </div>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">Cari</button>
                @if ($search !== '')
                    <a href="{{ route('trivia.index') }}" class="rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-900 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:hover:text-indigo-400">Reset</a>
                @endif
            </div>
        </form>

        @if ($trivias->isEmpty())
            <div class="mt-8 rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-base font-semibold text-slate-700 dark:text-slate-200">
                    {{ $search !== '' ? 'Tidak ada trivia yang cocok dengan pencarian.' : 'Belum ada trivia.' }}
                </p>
            </div>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($trivias as $trivia)
                    <button type="button" onclick="openMediaLightbox(this)"
                            data-lightbox-image="{{ $trivia->image ? Storage::url($trivia->image) : '' }}"
                            data-lightbox-title="{{ $trivia->title }}"
                            data-lightbox-description="{{ $trivia->description }}"
                            class="flex w-full cursor-zoom-in flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                        @if ($trivia->image)
                            <img src="{{ Storage::url($trivia->image) }}" alt="{{ $trivia->title }}" class="aspect-video w-full object-cover" loading="lazy" />
                        @endif
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ $trivia->title }}</h3>
                            @if ($trivia->description)
                                <p class="mt-2 line-clamp-4 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $trivia->description }}</p>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>

            <div class="mt-10">{{ $trivias->links() }}</div>
        @endif
    </section>
@endsection
