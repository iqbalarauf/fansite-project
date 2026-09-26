@extends('layouts.public', ['title' => $product->name, 'active' => 'merchandise'])

@section('content')
    @php
        $images = $product->imageUrls();
    @endphp

    <section class="mx-auto max-w-7xl px-4 pt-12 pb-16 sm:px-6 lg:px-8 lg:pb-24">
        <a href="{{ route('merchandise.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Semua Merchandise
        </a>

        <div class="mt-6 grid gap-8 lg:grid-cols-2">
            {{-- Kolase foto --}}
            <div>
                @if ($images === [])
                    <div class="flex aspect-square w-full items-center justify-center rounded-[2rem] border border-dashed border-slate-300 bg-white text-2xl font-black text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-indigo-400">
                        {{ \Illuminate\Support\Str::of($product->name)->substr(0, 1) }}
                    </div>
                @else
                    <button type="button" onclick="openMediaLightbox(this)" class="block w-full cursor-zoom-in overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                            data-lightbox-image="{{ $images[0] }}" data-lightbox-title="{{ $product->name }}" data-lightbox-description="{{ $product->description }}">
                        <img src="{{ $images[0] }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover" loading="eager" fetchpriority="high" decoding="async" />
                    </button>

                    @if (count($images) > 1)
                        <div class="mt-4 grid grid-cols-4 gap-3">
                            @foreach ($images as $image)
                                <button type="button" onclick="openMediaLightbox(this)" class="cursor-zoom-in overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
                                        data-lightbox-image="{{ $image }}" data-lightbox-title="{{ $product->name }}" data-lightbox-description="{{ $product->description }}">
                                    <img src="{{ $image }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover transition hover:opacity-95" loading="lazy" decoding="async" />
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Detail --}}
            <div class="flex flex-col">
                <h1 class="text-3xl font-black leading-tight text-slate-900 dark:text-white sm:text-4xl">{{ $product->name }}</h1>

                @if ($product->price !== null)
                    <p class="mt-4 text-2xl font-black text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
                @endif

                @if ($product->description)
                    <p class="mt-5 whitespace-pre-line text-base leading-8 text-slate-600 dark:text-slate-300">{{ $product->description }}</p>
                @endif

                @if ($shopUrl)
                    <div class="mt-8">
                        <a href="{{ $shopUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                            {{ __('Belanja') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
