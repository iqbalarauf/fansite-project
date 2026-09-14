<div id="media-lightbox" class="fixed inset-0 z-[110] hidden" role="dialog" aria-modal="true" aria-labelledby="media-lightbox-title">
    <div id="media-lightbox-backdrop" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>

    <div class="relative z-10 mx-auto flex h-full max-w-4xl items-center justify-center p-4">
        <div class="max-h-full w-full overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-end p-2">
                <button type="button" id="media-lightbox-close" aria-label="Tutup"
                        class="flex size-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-4 pb-6">
                <img id="media-lightbox-image" src="" alt="" class="mx-auto hidden max-h-[70vh] w-auto max-w-full rounded-xl object-contain" />

                <div class="mt-4">
                    <h2 id="media-lightbox-title" class="hidden text-2xl font-black text-slate-900 dark:text-white"></h2>
                    <p id="media-lightbox-description" class="mt-2 hidden whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-zinc-300"></p>
                    <p id="media-lightbox-credit" class="mt-3 hidden text-xs text-slate-500 dark:text-zinc-400"></p>
                </div>
            </div>
        </div>
    </div>
</div>
