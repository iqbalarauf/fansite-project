@extends('layouts.error', [
    'code' => 404,
    'title' => '404 — Halaman Tidak Ditemukan',
    'message' => 'Halaman yang kamu cari tidak ada, sudah dipindahkan, atau sedang tidak tersedia.',
])

@section('actions')
    <a
        href="{{ route('home') }}"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
    >
        Kembali ke Halaman Utama
    </a>
@endsection
