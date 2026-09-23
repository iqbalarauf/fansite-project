@extends('layouts.public', [
    'title' => $post->meta_title ?: $post->title,
    'active' => $section->value,
    'metaDescription' => $post->meta_description ?: $post->excerpt,
    'ogImage' => $post->og_image
        ? \Illuminate\Support\Facades\Storage::url($post->og_image)
        : ($post->cover ? \Illuminate\Support\Facades\Storage::url($post->cover) : null),
])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <article>
        <div class="mx-auto max-w-3xl px-4 pt-12 sm:px-6 lg:px-8">
            <a href="{{ route($section->publicIndexRoute()) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Semua {{ $section->label() }}
            </a>

            @if ($post->category)
                <span class="mt-6 inline-flex w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">{{ $post->category->name }}</span>
            @endif

            <h1 class="mt-4 text-3xl font-black leading-tight text-slate-900 dark:text-white sm:text-5xl">{{ $post->title }}</h1>

            <p class="mt-5 text-sm text-slate-500 dark:text-slate-400">
                {{ $post->published_at?->timezone(\App\Support\Timezone::display())?->locale('id')->isoFormat('D MMMM YYYY') }}
            </p>
        </div>

        @if ($post->cover)
            <div class="mx-auto mt-8 max-w-3xl px-4 sm:px-6 lg:px-8">
                <img src="{{ Storage::url($post->cover) }}" alt="{{ $post->title }}" class="aspect-video w-full rounded-[2rem] border border-slate-200 object-cover shadow-lg dark:border-slate-800" loading="eager" fetchpriority="high" decoding="async" />
            </div>
        @endif

        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            @if ($post->excerpt)
                <p class="border-l-4 border-indigo-300 pl-4 text-lg font-medium leading-8 text-slate-600 dark:border-indigo-700 dark:text-slate-300">{{ $post->excerpt }}</p>
            @endif

            <div class="rich-content mt-8">
                {!! \App\Support\HtmlSanitizer::article($post->content) !!}
            </div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Artikel Lainnya</h2>
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <a href="{{ $item->publicUrl() }}" class="flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                        <div class="overflow-hidden bg-slate-100 dark:bg-slate-800">
                            @if ($item->cover)
                                <img src="{{ Storage::url($item->cover) }}" alt="{{ $item->title }}" class="aspect-video w-full object-cover" loading="lazy" decoding="async" />
                            @else
                                <div class="flex aspect-video w-full items-center justify-center text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ \Illuminate\Support\Str::of($item->title)->substr(0, 1) }}</div>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="line-clamp-2 text-base font-black text-slate-900 dark:text-white">{{ $item->title }}</h3>
                            <p class="mt-auto pt-3 text-xs text-slate-500 dark:text-slate-400">{{ $item->published_at?->timezone(\App\Support\Timezone::display())?->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection