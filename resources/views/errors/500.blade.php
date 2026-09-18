@extends('layouts.error', [
    'code' => 500,
    'title' => '500 — Terjadi Kesalahan',
    'message' => 'Terjadi kesalahan pada server saat memproses permintaanmu. Silakan coba lagi beberapa saat, ya.',
])

@section('actions')
    <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
        <a
            href="{{ route('home') }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
        >
            Kembali ke Halaman Utama
        </a>
        <button
            type="button"
            onclick="location.reload()"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
        >
            Muat Ulang Halaman
        </button>
    </div>
@endsection
