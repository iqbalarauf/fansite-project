@php
    $base = $path;
    $source = data_get($this, "{$base}.data.source") ?? 'url';
    $hasStoredFile = filled(data_get($this, "{$base}.data.storage_path"));
@endphp

<div class="space-y-2">
    <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Image source') }}</flux:text>
    <div class="flex flex-wrap gap-4">
        <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
            <input type="radio" wire:model.live="{{ $base }}.data.source" value="url" class="rounded border-zinc-300 text-blue-600">
            {{ __('URL') }}
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
            <input type="radio" wire:model.live="{{ $base }}.data.source" value="upload" class="rounded border-zinc-300 text-blue-600">
            {{ __('Upload') }}
        </label>
    </div>
</div>

@if ($source === 'upload')
    <div class="space-y-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
        @if ($this->selectedImagePreviewUrl())
            <img src="{{ $this->selectedImagePreviewUrl() }}" alt="" class="max-h-40 w-full rounded-xl object-cover">
        @endif
        <input type="file" wire:model="imageUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
        <div class="flex flex-wrap gap-2">
            @if ($this->imageUpload)
                <flux:button wire:click="uploadImage" size="sm" variant="primary" icon="arrow-up-tray">{{ __('Upload') }}</flux:button>
            @endif
            @if ($hasStoredFile)
                <flux:button wire:click="removeImage" size="sm" variant="danger" icon="trash">{{ __('Remove image') }}</flux:button>
            @endif
        </div>
        <flux:error name="imageUpload" />
    </div>
@else
    <flux:input wire:model.live="{{ $base }}.data.url" :label="__('Image URL')" type="url" placeholder="https://..." />
@endif

<flux:input wire:model.live="{{ $base }}.data.alt" :label="__('Alt text')" />

<flux:select wire:model.live="{{ $base }}.data.display" :label="__('Image display')">
    @foreach (['fit' => 'Fit (cover)', 'contain' => 'Contain', 'auto' => 'Auto height', 'original' => 'Original'] as $value => $label)
        <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
    @endforeach
</flux:select>
