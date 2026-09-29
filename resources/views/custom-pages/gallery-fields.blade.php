@php
    $base = $path;
    $images = data_get($this, "{$base}.data.images") ?? [];
    $imageCount = count($images);
    $initialCount = (int) (data_get($this, "{$base}.data.initial_count") ?? 3);
    $previews = $this->galleryImagePreviews();
@endphp

<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Gallery images') }}</flux:text>
        <flux:badge size="sm" :color="$imageCount >= 8 ? 'amber' : 'zinc'">{{ $imageCount }}/8</flux:badge>
    </div>

    @if ($imageCount > 0)
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
            @foreach ($previews as $index => $preview)
                <div class="group relative overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <img src="{{ $preview['url'] }}" alt="{{ $preview['alt'] }}" class="h-24 w-full object-cover">
                    <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-gradient-to-t from-black/70 to-transparent p-1.5">
                        <span class="text-[11px] font-semibold text-white">{{ $index + 1 }}</span>
                        <flux:button wire:click="removeGalleryImage({{ $index }})" icon="trash" size="xs" variant="danger" square :aria-label="__('Remove image')" />
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex h-24 items-center justify-center rounded-lg border border-dashed border-zinc-300 text-xs text-zinc-500 dark:border-zinc-600">{{ __('Belum ada gambar.') }}</div>
    @endif

    @if ($imageCount < 8)
        <div class="space-y-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
            <input type="file" wire:model="galleryUploads" accept="image/*" multiple class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pilih maksimal 8 gambar sekaligus.') }}</flux:text>
            @if (! empty($galleryUploads))
                <flux:button wire:click="uploadGalleryImages" size="sm" variant="primary" icon="arrow-up-tray">{{ __('Upload images') }}</flux:button>
            @endif
            <flux:error name="galleryUploads" />
            <flux:error name="galleryUploads.*" />
        </div>
    @endif

    <flux:select wire:model.live="{{ $base }}.data.initial_count" :label="__('Jumlah gambar tampil awal')">
        @for ($count = 1; $count <= 8; $count++)
            <flux:select.option value="{{ $count }}">{{ $count }}</flux:select.option>
        @endfor
    </flux:select>
    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Jika jumlah gambar melebihi angka ini, sisanya ditampilkan lewat carousel.') }}</flux:text>
</div>
