@extends('layouts.public', ['title' => $section->label(), 'active' => $section->value])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <section class="bg-gradient-to-br from-indigo-950 via-slate-950 to-violet-950">
        <div class="mx-auto flex max-w-7xl flex-col items-start px-4 py-20 text-left sm:px-6 lg:px-8 lg:py-24">
            <span class="mb-4 inline-flex w-fit rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-50">{{ $section->label() }}</span>
            <h1 class="max-w-2xl text-4xl font-black leading-tight text-white sm:text-5xl">{{ $section->label() }}</h1>
            <p class="mt-4 max-w-xl text-base text-indigo-100 sm:text-lg">
                {{ $section->value === 'news' ? 'Kabar dan informasi terbaru seputar aktivitas idol.' : 'Catatan, ulasan, dan tulisan panjang dari tim fansite.' }}
            </p>
        </div>
    </section>

    @if ($featured)
        <section class="mx-auto max-w-7xl px-4 pt-16 pb-10 sm:px-6 lg:px-8">
            <span class="inline-flex w-fit rounded-full bg-yellow-300 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-slate-900">Sorotan</span>

            <a href="{{ $featured->publicUrl() }}" class="group mt-6 grid overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900 lg:grid-cols-2">
                <div class="overflow-hidden bg-slate-100 dark:bg-slate-800">
                    @if ($featured->cover)
                        <img src="{{ Storage::url($featured->cover) }}" alt="{{ $featured->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                    @else
                        <div class="flex aspect-video h-full items-center justify-center text-5xl font-black text-indigo-600 dark:text-indigo-400">{{ \Illuminate\Support\Str::of($featured->title)->substr(0, 1) }}</div>
                    @endif
                </div>
                <div class="flex flex-col justify-center p-6 sm:p-10">
                    @if ($featured->category)
                        <span class="w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">{{ $featured->category->name }}</span>
                    @endif
                    <h2 class="mt-4 text-2xl font-black leading-tight text-slate-900 dark:text-white sm:text-3xl">{{ $featured->title }}</h2>
                    @if ($featured->excerpt)
                        <p class="mt-3 line-clamp-3 text-base leading-7 text-slate-600 dark:text-slate-300">{{ $featured->excerpt }}</p>
                    @endif
                    <p class="mt-5 text-sm text-slate-500 dark:text-slate-400">{{ $featured->published_at?->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                </div>
            </a>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 pt-8 pb-20 sm:px-6 lg:px-8">
        @if ($posts->isEmpty() && $featured === null)
            <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-base font-semibold text-slate-700 dark:text-slate-200">Belum ada artikel {{ $section->label() }}.</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Artikel yang sudah diterbitkan akan muncul di sini.</p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <article class="flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                        <a href="{{ $post->publicUrl() }}" class="overflow-hidden bg-slate-100 dark:bg-slate-800">
                            @if ($post->cover)
                                <img src="{{ Storage::url($post->cover) }}" alt="{{ $post->title }}" class="aspect-video w-full object-cover" loading="lazy" />
                            @else
                                <div class="flex aspect-video w-full items-center justify-center text-4xl font-black text-indigo-600 dark:text-indigo-400">{{ \Illuminate\Support\Str::of($post->title)->substr(0, 1) }}</div>
                            @endif
                        </a>
                        <div class="flex flex-1 flex-col p-5">
                            @if ($post->category)
                                <span class="w-fit rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">{{ $post->category->name }}</span>
                            @endif
                            <h3 class="mt-3 line-clamp-2 text-lg font-black text-slate-900 dark:text-white">
                                <a href="{{ $post->publicUrl() }}">{{ $post->title }}</a>
                            </h3>
                            @if ($post->excerpt)
                                <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $post->excerpt }}</p>
                            @endif
                            <p class="mt-auto pt-4 text-xs text-slate-500 dark:text-slate-400">{{ $post->published_at?->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @endif
    </section>
@endsection