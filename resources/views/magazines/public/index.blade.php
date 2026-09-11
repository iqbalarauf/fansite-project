@extends('layouts.public', ['title' => 'Majalah', 'active' => 'magazine'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <section class="bg-gradient-to-br from-indigo-950 via-slate-950 to-violet-950">
        <div class="mx-auto flex max-w-7xl flex-col items-start px-4 py-20 text-left sm:px-6 lg:px-8 lg:py-24">
            <span class="mb-4 inline-flex w-fit rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-50">Majalah</span>
            <h1 class="max-w-2xl text-4xl font-black leading-tight text-white sm:text-5xl">Majalah Digital</h1>
            <p class="mt-4 max-w-xl text-base text-indigo-100 sm:text-lg">Baca edisi terbaru dan telusuri arsip majalah yang bisa dibaca serta di-download kapan saja.</p>
        </div>
    </section>

    @if ($main)
        @php
            $mainCover = $main->cover ? Storage::url($main->cover) : null;
        @endphp

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <span class="inline-flex w-fit rounded-full bg-yellow-300 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-slate-900">Edisi Utama</span>

            <div class="mt-6 grid gap-8 overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
                <div class="overflow-hidden rounded-[1.5rem] bg-slate-100 dark:bg-slate-800">
                    @if ($mainCover)
                        <img src="{{ $mainCover }}" alt="{{ $main->title }}" class="h-full w-full object-cover" />
                    @else
                        <div class="flex aspect-[3/4] items-center justify-center text-4xl font-black text-indigo-600 dark:text-indigo-400">{{ \Illuminate\Support\Str::of($main->title)->substr(0, 1) }}</div>
                    @endif
                </div>

                <div class="flex flex-col">
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white sm:text-4xl">{{ $main->title }}</h2>
                    @if ($main->description)
                        <p class="mt-4 text-base leading-8 text-slate-600 dark:text-slate-300">{{ $main->description }}</p>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-6 text-sm text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ number_format($main->views) }} viewers
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            {{ number_format($main->downloads) }} downloads
                        </span>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-4 pt-2">
                        <a href="{{ route('magazine.show', $main) }}" class="rounded-full bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">Baca Sekarang</a>
                        <a href="{{ route('magazine.download', $main) }}" class="rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-900 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:hover:text-indigo-400">Download PDF</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Arsip Majalah</h2>
            <span class="text-sm text-slate-500 dark:text-slate-400">{{ $archive->count() }} edisi</span>
        </div>

        @if ($archive->isEmpty())
            <div class="mt-8 rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-base font-semibold text-slate-700 dark:text-slate-200">Belum ada arsip majalah.</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Edisi sebelumnya akan muncul di sini.</p>
            </div>
        @else
            <div class="mt-8 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-4">Majalah</th>
                                <th class="px-6 py-4">Deskripsi</th>
                                <th class="px-6 py-4 text-center">Viewers</th>
                                <th class="px-6 py-4 text-center">Downloads</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($archive as $magazine)
                                @php
                                    $cover = $magazine->cover ? Storage::url($magazine->cover) : null;
                                @endphp
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="h-16 w-12 shrink-0 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
                                                @if ($cover)
                                                    <img src="{{ $cover }}" alt="{{ $magazine->title }}" class="h-full w-full object-cover" loading="lazy" />
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-lg font-black text-indigo-600 dark:text-indigo-400">{{ \Illuminate\Support\Str::of($magazine->title)->substr(0, 1) }}</div>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-900 dark:text-white">{{ $magazine->title }}</p>
                                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">/majalah/{{ $magazine->slug }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="max-w-md px-6 py-4 text-slate-600 dark:text-slate-300">
                                        <p class="line-clamp-2">{{ $magazine->description ?: '–' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-200">{{ number_format($magazine->views) }}</td>
                                    <td class="px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-200">{{ number_format($magazine->downloads) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('magazine.show', $magazine) }}" class="rounded-full bg-indigo-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-indigo-500">Baca</a>
                                            <a href="{{ route('magazine.download', $magazine) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-900 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:hover:text-indigo-400">Download</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>
@endsection