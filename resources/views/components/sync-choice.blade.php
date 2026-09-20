@props([
    'path',
    'current' => null,
])

@php
    $options = [
        [
            'value' => 'database',
            'icon' => 'circle-stack',
            'label' => __('Ambil nilai dari Database'),
            'active' => 'border-blue-400 bg-blue-50 text-blue-600 dark:border-blue-500/60 dark:bg-blue-950/40 dark:text-blue-300',
        ],
        [
            'value' => 'sheet',
            'icon' => 'table-cells',
            'label' => __('Ambil nilai dari Sheet'),
            'active' => 'border-green-400 bg-green-50 text-green-600 dark:border-green-500/60 dark:bg-green-950/40 dark:text-green-300',
        ],
        [
            'value' => 'skip',
            'icon' => 'no-symbol',
            'label' => __('Lewati (biarkan apa adanya)'),
            'active' => 'border-zinc-400 bg-zinc-100 text-zinc-600 dark:border-zinc-500 dark:bg-zinc-700 dark:text-zinc-200',
        ],
    ];
@endphp

<div {{ $attributes->class('inline-flex items-center gap-1') }}>
    @foreach ($options as $option)
        @php($isActive = $current === $option['value'])

        <flux:tooltip :content="$option['label']">
            <button
                type="button"
                wire:click="$set('{{ $path }}', '{{ $option['value'] }}')"
                aria-label="{{ $option['label'] }}"
                aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                class="inline-flex size-7 items-center justify-center rounded-md border transition {{ $isActive ? $option['active'] : 'border-zinc-200 text-zinc-400 hover:border-zinc-300 hover:text-zinc-600 dark:border-zinc-700 dark:text-zinc-500 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }}"
            >
                <flux:icon :icon="$option['icon']" variant="micro" />
            </button>
        </flux:tooltip>
    @endforeach
</div>
