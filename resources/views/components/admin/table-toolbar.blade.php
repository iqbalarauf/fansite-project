@props([
    'filters' => [],
    'showFilters' => false,
    'hasActiveFilters' => false,
    'searchPlaceholder' => __('Cari...'),
    'panelId' => 'filter-panel',
])

@php
    $perPage = (int) ($filters['per_page'] ?? 10);
    $search = (string) ($filters['search'] ?? '');
@endphp

<div
    x-data="{ filtersOpen: @js($hasActiveFilters) }"
    x-on:keydown.escape="filtersOpen = false"
>
    <div class="admin-table-toolbar">
        <label class="admin-toolbar-group">
            <span>{{ __('Show') }}</span>
            <select name="per_page" onchange="this.form.requestSubmit()" class="admin-toolbar-select" aria-label="{{ __('Jumlah baris per halaman') }}">
                @foreach ([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                @endforeach
            </select>
            <span>{{ __('entries') }}</span>
        </label>

        <div class="flex flex-wrap items-center gap-3">
            <div class="admin-toolbar-search">
                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                    <flux:icon icon="magnifying-glass" variant="outline" class="size-4" />
                </span>
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ $searchPlaceholder }}"
                    autocomplete="off"
                    x-on:input.debounce.500ms="$el.form && $el.form.requestSubmit()"
                />
            </div>

            @if ($showFilters)
                <button
                    type="button"
                    class="admin-filter-toggle"
                    x-bind:class="filtersOpen && 'is-active'"
                    x-on:click="filtersOpen = ! filtersOpen"
                    aria-controls="{{ $panelId }}"
                    x-bind:aria-expanded="filtersOpen"
                >
                    <flux:icon icon="funnel" variant="outline" class="size-4" />
                    {{ __('Filter') }}
                </button>
            @endif
        </div>
    </div>

    @if ($showFilters)
        <div id="{{ $panelId }}" class="admin-filter-panel" x-show="filtersOpen" x-cloak>
            {{ $slot }}
        </div>
    @endif
</div>
