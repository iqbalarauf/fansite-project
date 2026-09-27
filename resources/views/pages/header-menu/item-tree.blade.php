@php
    /**
     * Render satu level item menu (rekursif untuk submenu).
     *
     * @var array<int, array<string, mixed>>  $rows
     * @var array<int, array<int, array<string, mixed>>>  $childrenMap
     * @var array<string, string>  $typeLabels
     * @var int  $depth
     */
    $depth = $depth ?? 0;
    $indent = 6 + $depth * 20; // px menjorok ke kanan per level
    $isRoot = $depth === 0;
@endphp

@if ($depth > 0)
    <div
        style="margin-left: {{ $depth === 1 ? 24 : 20 }}px; width: calc(100% - {{ $depth === 1 ? 24 : 20 }}px)"
        class="border-l-2 border-dashed border-zinc-200 pl-4 dark:border-zinc-700"
    >
@endif

@if ($depth > 0 && $parentLabel)
    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Submenu dari :label', ['label' => $parentLabel]) }}</p>
@endif

<div wire:sort="sortItem" class="space-y-2">
    @foreach ($rows as $row)
        <div
            wire:sort:item="{{ $row['id'] }}"
            wire:key="menu-item-{{ $row['id'] }}"
            class="flex items-center gap-2 rounded-lg border py-2 pr-3
                {{ $isRoot
                    ? 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900'
                    : 'border-zinc-200 bg-zinc-50 py-1.5 pr-2 dark:border-zinc-700 dark:bg-zinc-800/60' }}"
        >
            <flux:icon
                wire:sort:handle
                name="bars-3"
                class="ms-2 shrink-0 cursor-grab text-zinc-400 {{ $isRoot ? 'size-4' : 'size-3.5' }}"
            />
            <div class="min-w-0 flex-1">
                <p class="truncate font-medium text-zinc-800 dark:text-zinc-200 {{ $isRoot ? 'text-sm' : 'text-xs text-zinc-700' }}">
                    {{ $row['label'] }}
                    <span class="ml-1 font-normal text-zinc-400 {{ $isRoot ? 'text-xs' : 'text-[11px]' }}">{{ $typeLabels[$row['type']] ?? $row['type'] }}</span>
                </p>
                @if ($row['url'])
                    <p class="truncate text-zinc-500 dark:text-zinc-400 {{ $isRoot ? 'text-xs' : 'text-[11px]' }}">{{ $row['url'] }}</p>
                @endif
            </div>

            <flux:button type="button" wire:click="edit({{ $row['id'] }})" size="{{ $isRoot ? 'sm' : 'xs' }}" variant="ghost" icon="pencil-square" :aria-label="__('Edit')" square />
            <flux:button type="button" wire:click="delete({{ $row['id'] }})" wire:confirm="Hapus item ini beserta submenunya?" size="{{ $isRoot ? 'sm' : 'xs' }}" variant="danger" icon="trash" :aria-label="__('Delete')" square />
        </div>

        @php $children = $childrenMap[$row['id']] ?? []; @endphp
        @if (! empty($children))
            @include('pages.header-menu.item-tree', [
                'rows' => $children,
                'childrenMap' => $childrenMap,
                'typeLabels' => $typeLabels,
                'depth' => $depth + 1,
                'parentLabel' => $row['label'],
            ])
        @endif
    @endforeach
</div>

@if ($depth > 0)
    </div>
@endif
