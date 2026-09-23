@php
    $current = data_get($this, $valuePath);
    $isHex = is_string($current) && preg_match('/^#[0-9A-Fa-f]{6}$/', $current) === 1;
    $resolvedHex = $isHex ? strtolower($current) : ($presetHexes[$current] ?? '#FFFFFF');
@endphp

<div class="space-y-3">
    <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $label }}</flux:text>

    <div class="flex items-center gap-2">
        <input type="color" wire:change="{{ $onChange }}($event.target.value)" value="{{ $resolvedHex }}"
            class="h-10 w-14 shrink-0 cursor-pointer rounded-lg border border-zinc-300 bg-transparent p-1 dark:border-zinc-600"
            :aria-label="$label">
        <input type="text" wire:change="{{ $onChange }}($event.target.value)" value="{{ $resolvedHex }}"
            placeholder="#FFFFFF" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @foreach ($presetHexes as $presetValue => $presetHex)
            <button type="button" wire:click="{{ $onPreset }}('{{ $presetValue }}')"
                title="{{ __($presetLabels[$presetValue]) }}"
                class="{{ $current === $presetValue ? 'ring-2 ring-indigo-500 ring-offset-2' : 'hover:ring-2 hover:ring-indigo-300 ring-offset-1' }} h-7 w-7 rounded-full border border-black/10 transition"
                style="background-color: {{ $presetHex }}"></button>
        @endforeach

        <button type="button" wire:click="{{ $onPreset }}('transparent')"
            title="{{ __('Transparent') }}"
            class="{{ $current === 'transparent' ? 'ring-2 ring-indigo-500 ring-offset-2' : 'hover:ring-2 hover:ring-indigo-300 ring-offset-1' }} h-7 w-7 shrink-0 rounded-full border border-black/10 bg-[length:10px_10px] bg-[position:0_0,5px_5px] transition"
            style="background-color: #ffffff; background-image: linear-gradient(45deg, #d4d4d8 25%, transparent 25%, transparent 75%, #d4d4d8 75%), linear-gradient(45deg, #d4d4d8 25%, transparent 25%, transparent 75%, #d4d4d8 75%)"></button>
    </div>
</div>