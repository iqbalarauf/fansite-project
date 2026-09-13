@extends('layouts.public', ['title' => 'Tentang '.($fanbaseName ?? 'Fansite'), 'active' => 'fansite'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <section class="bg-slate-950">
        <div class="bg-gradient-to-br from-indigo-950/85 via-slate-950/70 to-violet-950/80">
            <div class="mx-auto flex max-w-3xl flex-col items-center px-4 py-24 text-center sm:px-6 lg:py-28">
                <span class="mb-4 inline-flex w-fit rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-50">About Fansite</span>
                @if ($fanbaseLogo)
                    <img src="{{ Storage::url($fanbaseLogo) }}" alt="{{ $fanbaseName }}" class="mb-6 h-20 w-20 rounded-2xl object-cover shadow-lg ring-4 ring-white/10" />
                @endif
                <h1 class="text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">{{ $fanbaseName }}</h1>
                @if ($fanbaseDescription)
                    <p class="mt-5 max-w-xl text-base text-indigo-100 sm:text-lg">{{ $fanbaseDescription }}</p>
                @endif
            </div>
        </div>
    </section>

    @if (count($fanbaseActivities) > 0)
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <h2 class="text-3xl font-black text-slate-900 dark:text-white">Kegiatan Fanbase</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach ($fanbaseActivities as $activity)
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                            <span class="mt-1 flex size-6 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-black text-white">{{ $loop->iteration }}</span>
                            <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $activity }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (count($fanbaseGallery) > 0)
        <section class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-black text-slate-900 dark:text-white">Galeri</h2>
            <div class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-3">
                @foreach ($fanbaseGallery as $index => $path)
                    <div class="{{ $index === 0 ? 'col-span-2 row-span-2' : '' }} overflow-hidden rounded-[2rem] bg-white shadow-sm dark:bg-slate-900">
                        <img src="{{ Storage::url($path) }}" alt="Galeri {{ $fanbaseName }} {{ $index + 1 }}" class="h-full w-full object-cover" loading="lazy" />
                    </div>
                @endforeach
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

    <section class="mx-auto max-w-7xl px-4 py-4 pb-16 sm:px-6 lg:px-8">
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('about.idol', $idolSlug ?? '') }}" class="rounded-full bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">Tentang {{ $idolName ?? 'Idol' }}</a>
            <a href="{{ route('home') }}" class="rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-900 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:hover:text-indigo-400">Kembali ke Beranda</a>
        </div>
    </section>
@endsection