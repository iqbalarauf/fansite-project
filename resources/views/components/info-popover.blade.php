@props([
    'title' => null,
    'text' => null,
    'position' => 'bottom',
    'align' => 'start',
    'width' => 'w-80',
])

<div
    x-data="{ open: false }"
    x-on:keydown.escape.window="open = false"
    x-on:click.outside="open = false"
    data-info-popover
    {{ $attributes->class('relative inline-flex') }}
>
    <button
        type="button"
        x-on:click="open = ! open"
        x-bind:aria-expanded="open"
        aria-haspopup="dialog"
        aria-label="{{ $title ?: __('Info') }}"
        class="inline-flex size-5 shrink-0 items-center justify-center rounded-full border border-zinc-300 text-zinc-400 transition hover:border-indigo-400 hover:text-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-zinc-600 dark:text-zinc-500 dark:hover:border-indigo-500 dark:hover:text-indigo-400"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5">
            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        role="dialog"
        class="absolute z-50 {{ $width }} rounded-xl border border-zinc-200 bg-white p-4 text-left text-xs leading-6 text-zinc-600 shadow-xl dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 {{ $position === 'top' ? 'bottom-full mb-2' : 'top-full mt-2' }} {{ $align === 'start' ? 'left-0' : ($align === 'end' ? 'right-0' : 'left-1/2 -translate-x-1/2') }}"
    >
        @if ($title)
            <p class="mb-2 text-sm font-bold text-zinc-900 dark:text-white">{{ $title }}</p>
        @endif

        @if ($text)
            <p class="whitespace-pre-line">{{ $text }}</p>
        @endif

        {{ $slot }}
    </div>
</div>
