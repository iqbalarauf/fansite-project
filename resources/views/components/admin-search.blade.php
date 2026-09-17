@php($searchEndpoint = route('admin.search'))

<div
    x-data="{
        query: '',
        open: false,
        loading: false,
        groups: [],
        controller: null,
        endpoint: @js($searchEndpoint),
        async search() {
            const value = this.query.trim();

            if (value.length < 2) {
                this.controller?.abort();
                this.groups = [];
                this.loading = false;
                this.open = true;

                return;
            }

            this.open = true;
            this.loading = true;
            this.controller?.abort();
            this.controller = new AbortController();

            try {
                const response = await fetch(this.endpoint + '?q=' + encodeURIComponent(value), {
                    headers: { 'Accept': 'application/json' },
                    signal: this.controller.signal,
                });
                const data = await response.json();

                this.groups = data.groups ?? [];
            } catch (error) {
                if (error.name !== 'AbortError') {
                    this.groups = [];
                }
            } finally {
                this.loading = false;
            }
        },
        close() {
            this.open = false;
        },
        reset() {
            this.query = '';
            this.groups = [];
            this.open = false;
        },
        go(url) {
            this.close();

            if (window.Livewire && typeof window.Livewire.navigate === 'function') {
                window.Livewire.navigate(url);

                return;
            }

            window.location.href = url;
        },
    }"
    x-on:keydown.escape.window="close()"
    x-on:keydown.window.meta.k.prevent="$refs.search.focus(); open = true"
    x-on:keydown.window.ctrl.k.prevent="$refs.search.focus(); open = true"
    class="relative hidden w-full max-w-md md:block"
>
    <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-gray-400">
        <flux:icon icon="magnifying-glass" variant="outline" class="size-5" />
    </span>

    <input
        x-ref="search"
        x-model="query"
        x-on:input.debounce.250ms="search()"
        x-on:focus="open = query.trim().length >= 2"
        type="text"
        placeholder="{{ __('Cari atau ketik perintah...') }}"
        aria-label="{{ __('Pencarian') }}"
        autocomplete="off"
        class="h-11 w-full rounded-lg border border-gray-200 bg-transparent ps-11 pe-16 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-gray-200"
    />

    <span class="pointer-events-none absolute end-2.5 top-1/2 inline-flex -translate-y-1/2 items-center gap-0.5 rounded-lg border border-gray-200 bg-gray-50 px-[7px] py-[4.5px] text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
        <span>⌘</span><span>K</span>
    </span>

    <div
        x-cloak
        x-show="open"
        x-on:click.outside="close()"
        class="absolute inset-x-0 top-full z-50 mt-2 max-h-[26rem] overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-700 dark:bg-gray-900"
    >
        <div x-show="loading" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ __('Mencari...') }}</div>

        <div x-show="!loading && query.trim().length < 2" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
            {{ __('Ketik minimal 2 karakter untuk mencari.') }}
        </div>

        <div x-show="!loading && query.trim().length >= 2 && groups.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
            {{ __('Tidak ada hasil ditemukan.') }}
        </div>

        <template x-for="group in groups" :key="group.label">
            <div>
                <div class="px-4 pb-1 pt-3 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500" x-text="group.label"></div>

                <template x-for="item in group.items" :key="item.url + item.label">
                    <button
                        type="button"
                        x-on:click="go(item.url)"
                        class="flex w-full items-center gap-3 px-4 py-2 text-start transition hover:bg-gray-50 dark:hover:bg-gray-800"
                    >
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <flux:icon icon="document-text" variant="outline" class="size-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-gray-800 dark:text-gray-100" x-text="item.label"></span>
                            <span class="block truncate text-xs text-gray-500 dark:text-gray-400" x-text="item.description"></span>
                        </span>
                    </button>
                </template>
            </div>
        </template>
    </div>
</div>
