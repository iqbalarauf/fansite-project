@extends('layouts.error', [
    'code' => 403,
    'title' => '403 — Akses Ditolak',
    'message' => 'Kamu tidak memiliki izin untuk membuka halaman ini. Jika kamu merasa ini sebuah kesalahan, hubungi pengelola situs.',
])

@section('illustration')
    <p class="text-8xl font-black leading-none tracking-tight text-gray-800 dark:text-white sm:text-9xl">403</p>
@endsection

@section('actions')
    <a
        href="{{ route('home') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
    >
        Kembali ke Halaman Utama
    </a>
@endsection
