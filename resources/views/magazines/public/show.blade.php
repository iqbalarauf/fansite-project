@extends('layouts.public', ['title' => $magazine->title, 'active' => 'magazine'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;

        $fileUrl = Storage::url($magazine->file_path);
        $cover = $magazine->cover ? Storage::url($magazine->cover) : null;
    @endphp

    <section class="bg-gradient-to-br from-indigo-950 via-slate-950 to-violet-950">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <a href="{{ route('magazine.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-100 transition hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Semua Majalah
            </a>
            <h1 class="mt-6 max-w-3xl text-3xl font-black leading-tight text-white sm:text-5xl">{{ $magazine->title }}</h1>
            @if ($magazine->description)
                <p class="mt-4 max-w-2xl text-base text-indigo-100 sm:text-lg">{{ $magazine->description }}</p>
            @endif

            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('magazine.download', $magazine) }}" class="rounded-full bg-yellow-300 px-6 py-3 text-sm font-bold text-slate-900 shadow-lg shadow-yellow-200/40 transition hover:bg-yellow-200">Download PDF</a>
                <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="rounded-full border border-white/40 bg-white/10 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/15">Buka di Tab Baru</a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,4fr)]">
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="overflow-hidden bg-slate-100 dark:bg-slate-800">
                        @if ($cover)
                            <img src="{{ $cover }}" alt="{{ $magazine->title }}" class="aspect-[3/4] w-full object-cover" />
                        @else
                            <div class="flex aspect-[3/4] items-center justify-center text-4xl font-black text-indigo-600 dark:text-indigo-400">{{ \Illuminate\Support\Str::of($magazine->title)->substr(0, 1) }}</div>
                        @endif
                    </div>
                    <div class="space-y-3 p-5 text-sm text-slate-600 dark:text-slate-300">
                        <p class="flex items-center justify-between"><span>Viewers</span><span class="font-bold text-slate-900 dark:text-white">{{ number_format($magazine->views) }}</span></p>
                        <p class="flex items-center justify-between"><span>Downloads</span><span class="font-bold text-slate-900 dark:text-white">{{ number_format($magazine->downloads) }}</span></p>
                        <p class="flex items-center justify-between"><span>Diterbitkan</span><span class="font-bold text-slate-900 dark:text-white">{{ $magazine->created_at?->locale('id')->isoFormat('D MMM YYYY') }}</span></p>
                    </div>
                </div>
            </aside>

            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Pembaca PDF</p>
                </div>
                <iframe src="{{ $fileUrl }}#toolbar=1&view=FitH" title="{{ $magazine->title }}" class="h-[80vh] w-full bg-slate-100 dark:bg-slate-800"></iframe>
            </div>
        </div>
    </section>
@endsection