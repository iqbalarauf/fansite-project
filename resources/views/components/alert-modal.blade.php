@props([
    'zIndex' => 'z-[9999]',
])

<div
    x-data="{
        open: false,
        mode: 'alert',
        variant: 'info',
        title: '',
        message: '',
        confirmText: 'OK',
        cancelText: 'Batal',
        resolver: null,
        openAlert(detail = {}) {
            this.setup(Object.assign({}, detail, { mode: 'alert', confirmText: detail.confirmText || 'OK', variant: detail.variant || 'info' }));
        },
        openConfirm(detail = {}) {
            this.setup(Object.assign({}, detail, { mode: 'confirm', confirmText: detail.confirmText || 'Ya', cancelText: detail.cancelText || 'Batal', variant: detail.variant || 'warning' }));
        },
        setup(detail) {
            this.mode = detail.mode;
            this.variant = ['success', 'error', 'warning', 'info'].includes(detail.variant) ? detail.variant : 'info';
            this.title = detail.title || '';
            this.message = detail.message || '';
            this.confirmText = detail.confirmText;
            this.cancelText = detail.cancelText || 'Batal';
            this.resolver = typeof detail.resolver === 'function' ? detail.resolver : null;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        accept() {
            this.close(true);
        },
        cancel() {
            this.close(false);
        },
        close(result) {
            this.open = false;
            document.body.style.overflow = '';
            const resolver = this.resolver;
            this.resolver = null;
            if (resolver) {
                resolver(result);
            }
        },
        iconWrapperClass() {
            return {
                success: 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400',
                error: 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
                warning: 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400',
                info: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400',
            }[this.variant];
        },
        confirmButtonClass() {
            return {
                success: 'bg-green-600 text-white hover:bg-green-500',
                error: 'bg-red-600 text-white hover:bg-red-500',
                warning: 'bg-amber-500 text-white hover:bg-amber-400',
                info: 'bg-indigo-600 text-white hover:bg-indigo-500',
            }[this.variant];
        },
    }"
    x-on:app-alert.window="openAlert($event.detail)"
    x-on:app-confirm.window="openConfirm($event.detail)"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="cancel()"
    data-alert-modal
    role="dialog"
    aria-modal="true"
    class="fixed inset-0 flex items-center justify-center overflow-y-auto p-5 {{ $zIndex }}"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        @click="cancel()"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 h-full w-full bg-slate-900/50 backdrop-blur-sm"
    ></div>

    {{-- Dialog --}}
    <div
        x-show="open"
        @click.stop
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900"
    >
        <div class="flex items-start gap-4">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-full" :class="iconWrapperClass()" aria-hidden="true">
                <svg x-show="variant === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-6">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                </svg>
                <svg x-show="variant === 'error'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-6">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm0-13a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 9.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                </svg>
                <svg x-show="variant === 'warning'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-6">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.19-1.458-1.515-2.625L8.485 2.495ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                </svg>
                <svg x-show="variant === 'info'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-6">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                </svg>
            </span>

            <div class="min-w-0 flex-1">
                <h3 x-show="title !== ''" x-text="title" class="text-base font-bold text-slate-900 dark:text-white"></h3>
                <p x-text="message" class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"></p>
            </div>

            <button type="button" @click="cancel()" aria-label="{{ __('Tutup') }}"
                    class="flex size-8 shrink-0 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
            </button>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <button type="button" x-show="mode === 'confirm'" @click="cancel()" x-text="cancelText"
                    class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-600"></button>
            <button type="button" @click="accept()" x-text="confirmText"
                    class="inline-flex items-center rounded-full px-5 py-2 text-sm font-bold transition" :class="confirmButtonClass()"></button>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
