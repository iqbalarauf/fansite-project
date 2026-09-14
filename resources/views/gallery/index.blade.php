@extends('layouts.public', ['title' => 'Galeri', 'active' => 'gallery'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <section class="bg-gradient-to-br from-indigo-950 via-slate-950 to-violet-950">
        <div class="mx-auto flex max-w-7xl flex-col items-start px-4 py-20 text-left sm:px-6 lg:px-8 lg:py-24">
            <span class="mb-4 inline-flex w-fit rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-50">Galeri</span>
            <h1 class="max-w-2xl text-4xl font-black leading-tight text-white sm:text-5xl">Galeri Fansite</h1>
            <p class="mt-4 max-w-xl text-base text-indigo-100 sm:text-lg">Kumpulan foto dan video dari fansite.</p>
        </div>
    </section>

    @if ($photos)
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Foto</h2>

            @if ($photos->isEmpty())
                <div class="mt-6 rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-base font-semibold text-slate-700 dark:text-slate-200">Belum ada foto galeri.</p>
                </div>
            @else
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($photos as $photo)
                        <figure class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <button type="button" onclick="openMediaLightbox(this)" class="block w-full cursor-zoom-in"
                                    data-lightbox-image="{{ Storage::url($photo->photo) }}"
                                    data-lightbox-description="{{ $photo->description }}"
                                    data-lightbox-credit="{{ $photo->credit_photographer ? 'Credit: '.$photo->credit_photographer : '' }}">
                                <img src="{{ Storage::url($photo->photo) }}" alt="{{ $photo->description ?: 'Gallery photo' }}" class="aspect-[4/3] w-full object-cover transition duration-500 hover:opacity-95" loading="lazy" />
                            </button>
                            @if ($photo->description || $photo->credit_photographer)
                                <figcaption class="p-5">
                                    @if ($photo->description)
                                        <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $photo->description }}</p>
                                    @endif
                                    @if ($photo->credit_photographer)
                                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Credit: {{ $photo->credit_photographer }}</p>
                                    @endif
                                </figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>

                <div class="mt-10">{{ $photos->links() }}</div>
            @endif
        </section>
    @endif

    @if ($videos)
        <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Video</h2>

            @if ($videos->isEmpty())
                <div class="mt-6 rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-base font-semibold text-slate-700 dark:text-slate-200">Belum ada video galeri.</p>
                </div>
            @else
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($videos as $video)
                        @include('partials.gallery-video', ['video' => $video])
                    @endforeach
                </div>

                <div class="mt-10">{{ $videos->links() }}</div>
            @endif
        </section>
    @endif

    @if ($videos && $videos->isNotEmpty())
        <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
        <script async src="https://www.tiktok.com/embed.js"></script>
    @endif
@endsection
