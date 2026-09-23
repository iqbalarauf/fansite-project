@php
    $base = $path;
@endphp

<flux:input wire:model.live="{{ $base }}.data.label" :label="__('Label')" />
<flux:input wire:model.live="{{ $base }}.data.url" :label="__('Link URL')" type="url" />
<flux:select wire:model.live="{{ $base }}.data.alignment" :label="__('Button alignment')">
    @foreach (['left' => 'Left', 'center' => 'Center', 'right' => 'Right'] as $value => $label)
        <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
    @endforeach
</flux:select>
<flux:input wire:model.live="{{ $base }}.data.bg_color" :label="__('Button color')" type="color" />
<flux:input wire:model.live="{{ $base }}.data.text_color" :label="__('Label color')" type="color" />
