@extends('layouts.public', ['title' => 'Merchandise', 'active' => 'merchandise'])

@section('content')
    <section class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 lg:px-8">
        <h1 class="mt-4 max-w-2xl text-4xl font-black leading-tight text-slate-900 dark:text-white sm:text-5xl">Merchandise Fanbase</h1>
        <p class="mt-4 max-w-xl text-base text-slate-600 dark:text-slate-300 sm:text-lg">Produk merchandise yang dijual.</p>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($products->isEmpty())
            <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-base font-semibold text-slate-700 dark:text-slate-200">Belum ada produk merchandise.</p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    @php
                        $productShopUrl = $product->shop_url ?: $shopUrl;
                        $cover = $product->coverUrl();
                    @endphp
                    <article class="flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                        <a href="{{ route('merchandise.show', $product) }}" class="block">
                            <div class="aspect-[4/3] w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                                @if ($cover)
                                    <img src="{{ $cover }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 hover:scale-105" loading="lazy" decoding="async" />
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ \Illuminate\Support\Str::of($product->name)->substr(0, 1) }}</div>
                                @endif
                            </div>
                        </a>

                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="line-clamp-2 text-lg font-black text-slate-900 dark:text-white">
                                <a href="{{ route('merchandise.show', $product) }}" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ $product->name }}</a>
                            </h3>
                            @if ($product->price !== null)
                                <p class="mt-2 text-base font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
                            @endif
                            @if ($product->description)
                                <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $product->description }}</p>
                            @endif

                            <div class="mt-auto flex flex-col gap-2 pt-5 sm:flex-row">
                                <a href="{{ route('merchandise.show', $product) }}" class="inline-flex w-full items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-200 dark:hover:text-indigo-400 sm:w-auto">{{ __('Detail') }}</a>
                                @if ($productShopUrl)
                                    <a href="{{ $productShopUrl }}" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500 sm:w-auto">{{ __('Belanja') }}</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
