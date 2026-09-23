@php($base = $path)

<flux:textarea wire:model.live="{{ $base }}.data.text" :label="__('Text')" rows="6" />
<flux:select wire:model.live="{{ $base }}.data.heading" :label="__('Heading')">
    @foreach (['none' => 'Paragraph', 'h1' => 'Heading 1', 'h2' => 'Heading 2', 'h3' => 'Heading 3', 'h4' => 'Heading 4'] as $value => $label)
        <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
    @endforeach
</flux:select>
<flux:select wire:model.live="{{ $base }}.data.font_size" :label="__('Font size')">
    @foreach (['sm' => 'Small', 'base' => 'Base', 'lg' => 'Large', 'xl' => 'XL', '2xl' => '2XL', '3xl' => '3XL', '4xl' => '4XL'] as $value => $label)
        <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
    @endforeach
</flux:select>
<flux:select wire:model.live="{{ $base }}.data.alignment" :label="__('Text alignment')">
    @foreach (['left' => 'Left', 'center' => 'Center', 'right' => 'Right', 'justify' => 'Justify'] as $value => $label)
        <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
    @endforeach
</flux:select>
<flux:input wire:model.live="{{ $base }}.data.color" :label="__('Text color')" type="color" />
<div class="flex flex-wrap gap-3">
    <flux:checkbox wire:model.live="{{ $base }}.data.bold" :label="__('Bold')" />
    <flux:checkbox wire:model.live="{{ $base }}.data.italic" :label="__('Italic')" />
    <flux:checkbox wire:model.live="{{ $base }}.data.underline" :label="__('Underline')" />
</div>
