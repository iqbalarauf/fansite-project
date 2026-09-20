<?php

use App\Support\SettingsStore;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Features Activation')] class extends Component
{
    public bool $newsEnabled = true;

    public bool $blogEnabled = true;

    public bool $magazineEnabled = true;

    public bool $triviaEnabled = true;

    public bool $photoboothEnabled = true;

    public bool $sheetIntegrationEnabled = false;

    public string $galleryMode = 'photos';

    public string $welcomeFeedSource = 'news';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $settings = DB::table('app_settings')->pluck('value', 'key')->all();

        $this->newsEnabled = filter_var($settings['news_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->blogEnabled = filter_var($settings['blog_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->magazineEnabled = filter_var($settings['magazines_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->triviaEnabled = filter_var($settings['trivia_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->photoboothEnabled = filter_var($settings['photobooth_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->sheetIntegrationEnabled = filter_var($settings['sheet_integration_enabled'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

        $galleryMode = (string) ($settings['gallery_mode'] ?? 'photos');
        $this->galleryMode = in_array($galleryMode, ['photos', 'videos', 'both'], true) ? $galleryMode : 'photos';

        $feedSource = (string) ($settings['welcome_feed_source'] ?? 'news');
        $this->welcomeFeedSource = in_array($feedSource, ['news', 'blog', 'magazines', 'trivia'], true) ? $feedSource : 'news';
    }

    public function save(): void
    {
        $this->validate([
            'galleryMode' => ['required', 'in:photos,videos,both'],
            'welcomeFeedSource' => ['required', 'in:news,blog,magazines,trivia'],
        ]);

        SettingsStore::setApp([
            'news_enabled' => $this->newsEnabled ? 'true' : 'false',
            'blog_enabled' => $this->blogEnabled ? 'true' : 'false',
            'magazines_enabled' => $this->magazineEnabled ? 'true' : 'false',
            'trivia_enabled' => $this->triviaEnabled ? 'true' : 'false',
            'photobooth_enabled' => $this->photoboothEnabled ? 'true' : 'false',
            'sheet_integration_enabled' => $this->sheetIntegrationEnabled ? 'true' : 'false',
            'gallery_mode' => $this->galleryMode,
            'welcome_feed_source' => $this->welcomeFeedSource,
        ]);

        Flux::toast(variant: 'success', text: __('Features updated.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Features Activation') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Features Activation')" :subheading="__('Aktifkan atau nonaktifkan fitur konten yang tampil di situs publik dan panel admin.')">
        <form wire:submit="save" class="space-y-6">
            <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="newsEnabled" value="1" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">
                        <span class="font-medium">{{ __('News') }}</span>
                        <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __('Tampilkan menu dan halaman publik News.') }}</span>
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="blogEnabled" value="1" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">
                        <span class="font-medium">{{ __('Blog') }}</span>
                        <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __('Tampilkan menu dan halaman publik Blog.') }}</span>
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="magazineEnabled" value="1" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">
                        <span class="font-medium">{{ __('Majalah') }}</span>
                        <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __('Tampilkan menu dan halaman publik Majalah.') }}</span>
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="triviaEnabled" value="1" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">
                        <span class="font-medium">{{ __('Trivia') }}</span>
                        <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __('Tampilkan menu dan halaman publik Trivia.') }}</span>
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="photoboothEnabled" value="1" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">
                        <span class="font-medium">{{ __('Photobooth') }}</span>
                        <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __('Tampilkan menu dan halaman publik Photobooth.') }}</span>
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="sheetIntegrationEnabled" value="1" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">
                        <span class="font-medium">{{ __('Sheet Integration') }}</span>
                        <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __('Tampilkan menu Sheet Integration (sinkronisasi Master Data dengan Google Sheet). Nonaktif secara default.') }}</span>
                    </span>
                </label>
            </div>

            <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <div>
                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Galeri — Tampilan Publik') }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pilih konten yang ditampilkan di halaman Galeri publik.') }}</p>
                </div>

                @foreach (['photos' => 'Foto', 'videos' => 'Video', 'both' => 'Keduanya'] as $value => $label)
                    <label class="flex items-start gap-3">
                        <input type="radio" wire:model="galleryMode" value="{{ $value }}" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ __($label) }}</span>
                    </label>
                @endforeach

                @error('galleryMode') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <div>
                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Kartu "Berita Terbaru" (Welcome)') }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pilih sumber konten untuk kartu di halaman Welcome.') }}</p>
                </div>

                @foreach (['news' => 'News', 'blog' => 'Blog', 'magazines' => 'Majalah', 'trivia' => 'Trivia'] as $value => $label)
                    <label class="flex items-start gap-3">
                        <input type="radio" wire:model="welcomeFeedSource" value="{{ $value }}" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ __($label) }}</span>
                    </label>
                @endforeach

                @error('welcomeFeedSource') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
