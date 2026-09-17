@php
    $first = $paginator->firstItem() ?? 0;
    $last = $paginator->lastItem() ?? 0;
@endphp

<div class="flex flex-col gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
        {{ __('Showing') }}
        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $first }}</span>
        {{ __('to') }}
        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $last }}</span>
        {{ __('of') }}
        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $paginator->total() }}</span>
        {{ __('entries') }}
    </p>

    @if ($paginator->hasPages())
        <div class="flex items-center gap-1">
            <a href="{{ $paginator->url(1) }}"
               aria-label="{{ __('Halaman pertama') }}"
               class="flex size-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700/40 {{ $paginator->onFirstPage() ? 'pointer-events-none opacity-40' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"/></svg>
            </a>
            <a href="{{ $paginator->previousPageUrl() }}"
               aria-label="{{ __('Halaman sebelumnya') }}"
               class="flex size-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700/40 {{ $paginator->onFirstPage() ? 'pointer-events-none opacity-40' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </a>

            <span class="flex items-center gap-1.5 px-2 text-theme-sm text-gray-600 dark:text-gray-300">
                {{ __('Page') }} {{ $paginator->currentPage() }} {{ __('of') }} {{ $paginator->lastPage() }}
            </span>

            <a href="{{ $paginator->nextPageUrl() }}"
               aria-label="{{ __('Halaman berikutnya') }}"
               class="flex size-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700/40 {{ ! $paginator->hasMorePages() ? 'pointer-events-none opacity-40' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
            <a href="{{ $paginator->url($paginator->lastPage()) }}"
               aria-label="{{ __('Halaman terakhir') }}"
               class="flex size-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700/40 {{ ! $paginator->hasMorePages() ? 'pointer-events-none opacity-40' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 4.5l7.5 7.5-7.5 7.5m6-15l7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>
    @endif
</div>
