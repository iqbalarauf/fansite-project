@php
    $base = $path;
    $playlistUrl = data_get($this, "{$base}.data.playlist_url") ?? '';
    $visibleCount = (int) (data_get($this, "{$base}.data.visible_count") ?? 3);
    $previewEmbed = filled($playlistUrl) ? \App\Support\YoutubeEmbed::embedUrl((string) $playlistUrl) : null;
@endphp

<flux:input
    wire:model.live="{{ $base }}.data.playlist_url"
    :label="__('YouTube playlist URL')"
    type="url"
    placeholder="https://www.youtube.com/playlist?list=..."
/>
<flux:error name="{{ $base }}.data.playlist_url" />

<flux:input
    wire:model.live="{{ $base }}.data.section_title"
    :label="__('Heading (optional)')"
    placeholder="Lihat konten terbaru"
/>

<flux:select wire:model.live="{{ $base }}.data.visible_count" :label="__('Cards visible at once')">
    @for ($count = 1; $count <= 4; $count++)
        <flux:select.option value="{{ $count }}">{{ $count }}</flux:select.option>
    @endfor
</flux:select>
<flux:error name="{{ $base }}.data.visible_count" />
<flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Jumlah kartu video yang tampil sekaligus; sisanya dapat digeser via tombol carousel.') }}</flux:text>

@if ($previewEmbed)
    <div class="space-y-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
        <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Preview playlist') }}</flux:text>
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <iframe src="{{ $previewEmbed }}" title="YouTube playlist" class="aspect-video w-full" allowfullscreen loading="lazy"></iframe>
        </div>
    </div>
@endif
