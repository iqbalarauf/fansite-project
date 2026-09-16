@extends('layouts.public', ['title' => '500 — Terjadi Kesalahan', 'active' => 'home'])

@section('content')
    <section class="mx-auto flex w-full max-w-6xl flex-col items-center justify-center px-4 py-20 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl rounded-[2rem] border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-12">
            <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Error 500</p>
            <h1 class="mt-4 text-7xl font-black text-slate-900 dark:text-white sm:text-8xl">500</h1>
            <h2 class="mt-4 text-2xl font-black text-slate-900 dark:text-white">Terjadi Kesalahan</h2>
            <p class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-300">
                Terjadi kesalahan pada server saat memproses permintaanmu. Silakan coba lagi beberapa saat, ya.
            </p>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('home') }}" class="rounded-full bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
                    Kembali ke Beranda
                </a>
                <button type="button" onclick="location.reload()" class="rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:border-indigo-400 hover:text-indigo-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:text-indigo-400">
                    Muat Ulang Halaman
                </button>
            </div>
        </div>
    </section>
@endsection