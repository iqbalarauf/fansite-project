@if ($paginator->hasPages())
    <div class="flex items-center justify-between border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </p>
        <div class="flex items-center gap-1">
            <a href="{{ $paginator->url(1) }}"
               class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 {{ $paginator->onFirstPage() ? 'pointer-events-none opacity-40' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"/></svg>
            </a>
            <a href="{{ $paginator->previousPageUrl() }}"
               class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 {{ $paginator->onFirstPage() ? 'pointer-events-none opacity-40' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </a>

            <span class="flex items-center gap-1.5 px-1 text-sm text-zinc-600 dark:text-zinc-300">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            <a href="{{ $paginator->nextPageUrl() }}"
               class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 {{ ! $paginator->hasMorePages() ? 'pointer-events-none opacity-40' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
            <a href="{{ $paginator->url($paginator->lastPage()) }}"
               class="flex size-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 {{ ! $paginator->hasMorePages() ? 'pointer-events-none opacity-40' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 4.5l7.5 7.5-7.5 7.5m6-15l7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>
        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <span>Rows:</span>
            @foreach ([10, 25, 50, 100] as $size)
                <a href="{{ request()->fullUrlWithQuery(['per_page' => $size, $pageParam => 1]) }}"
                   class="flex size-8 items-center justify-center rounded-lg border text-xs
                       {{ $perPage == $size
                           ? 'border-blue-500 bg-blue-50 text-blue-600 font-medium dark:bg-blue-950 dark:text-blue-400'
                           : 'border-zinc-200 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800' }}">
                    {{ $size }}
                </a>
            @endforeach
        </div>
    </div>
@else
    <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
        <p class="text-sm text-zinc-500 dark:text-zinc-400">Showing {{ $paginator->total() }} results</p>
    </div>
@endif
