<?php

use App\Models\CustomPage;
use App\Support\HeroLink;
use App\Support\ImageOptimizer;
use App\Support\SettingsStore;
use App\Support\YoutubeEmbed;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Landing Page')] class extends Component
{
    use WithFileUploads;

    public string $titleText = 'Selamat Datang di Fansite';

    public string $titleColor = '#FFFFFF';

    public bool $nameAnimate = true;

    public string $name1Text = '';

    public string $name1Color = '#FDE047';

    public string $name2Text = '';

    public string $name2Color = '#FDE047';

    public string $galleryMode = 'photos';

    public string $welcomeFeedSource = 'news';

    public ?string $heroImagePath = null;

    public mixed $heroImageUpload = null;

    public string $heroImageDisplay = 'fit';

    public bool $heroButton1Enabled = true;

    public string $heroButton1Label = 'Lihat Profil';

    public string $heroButton1LinkType = 'url';

    public string $heroButton1LinkValue = '#about';

    public bool $heroButton2Enabled = true;

    public string $heroButton2Label = 'Jadwal Terbaru';

    public string $heroButton2LinkType = 'url';

    public string $heroButton2LinkValue = '#schedule';

    public bool $youtubeEmbedEnabled = false;

    public string $youtubePlaylistUrl = '';

    public string $youtubeDisplayMode = 'cards';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $settings = DB::table('app_settings')->pluck('value', 'key')->all();

        $this->titleText = (string) ($settings['welcome_title_text'] ?? 'Selamat Datang di Fansite');
        $this->titleColor = (string) ($settings['welcome_title_color'] ?? '#FFFFFF');
        $this->nameAnimate = filter_var($settings['welcome_name_animate'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->name1Text = (string) ($settings['welcome_name_1_text'] ?? '');
        $this->name1Color = (string) ($settings['welcome_name_1_color'] ?? '#FDE047');
        $this->name2Text = (string) ($settings['welcome_name_2_text'] ?? '');
        $this->name2Color = (string) ($settings['welcome_name_2_color'] ?? '#FDE047');

        $galleryMode = (string) ($settings['gallery_mode'] ?? 'photos');
        $this->galleryMode = in_array($galleryMode, ['photos', 'videos', 'both'], true) ? $galleryMode : 'photos';

        $feedSource = (string) ($settings['welcome_feed_source'] ?? 'news');
        $this->welcomeFeedSource = in_array($feedSource, ['news', 'blog', 'magazines', 'trivia'], true) ? $feedSource : 'news';

        $this->heroImagePath = $settings['hero_image'] ?? null;
        $heroImageDisplay = (string) ($settings['hero_image_display'] ?? 'fit');
        $this->heroImageDisplay = in_array($heroImageDisplay, ['fit', 'contain', 'adjustable', 'original'], true) ? $heroImageDisplay : 'fit';

        $this->heroButton1Enabled = filter_var($settings['hero_button_1_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->heroButton1Label = (string) ($settings['hero_button_1_label'] ?? 'Lihat Profil');
        $button1Type = (string) ($settings['hero_button_1_link_type'] ?? 'url');
        $this->heroButton1LinkType = in_array($button1Type, HeroLink::TYPES, true) ? $button1Type : 'url';
        $this->heroButton1LinkValue = (string) ($settings['hero_button_1_link_value'] ?? '#about');

        $this->heroButton2Enabled = filter_var($settings['hero_button_2_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->heroButton2Label = (string) ($settings['hero_button_2_label'] ?? 'Jadwal Terbaru');
        $button2Type = (string) ($settings['hero_button_2_link_type'] ?? 'url');
        $this->heroButton2LinkType = in_array($button2Type, HeroLink::TYPES, true) ? $button2Type : 'url';
        $this->heroButton2LinkValue = (string) ($settings['hero_button_2_link_value'] ?? '#schedule');

        $this->youtubeEmbedEnabled = filter_var($settings['youtube_embed_enabled'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $this->youtubePlaylistUrl = (string) ($settings['youtube_playlist_url'] ?? '');
        $youtubeMode = (string) ($settings['youtube_display_mode'] ?? 'cards');
        $this->youtubeDisplayMode = in_array($youtubeMode, ['cards', 'embed'], true) ? $youtubeMode : 'cards';
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $this->validate([
            'titleText' => ['required', 'string', 'max:120'],
            'titleColor' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'nameAnimate' => ['boolean'],
            'name1Text' => ['nullable', 'string', 'max:120'],
            'name1Color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'name2Text' => ['nullable', 'string', 'max:120'],
            'name2Color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'galleryMode' => ['required', 'in:photos,videos,both'],
            'welcomeFeedSource' => ['required', 'in:news,blog,magazines,trivia'],
            'heroImageUpload' => ['nullable', 'image', 'max:3072'],
            'heroImageDisplay' => ['required', 'in:fit,contain,adjustable,original'],
            'heroButton1Enabled' => ['boolean'],
            'heroButton1Label' => ['nullable', 'string', 'max:255'],
            'heroButton1LinkType' => ['required', 'in:url,page,list'],
            'heroButton1LinkValue' => ['nullable', 'string', 'max:2048'],
            'heroButton2Enabled' => ['boolean'],
            'heroButton2Label' => ['nullable', 'string', 'max:255'],
            'heroButton2LinkType' => ['required', 'in:url,page,list'],
            'heroButton2LinkValue' => ['nullable', 'string', 'max:2048'],
            'youtubeEmbedEnabled' => ['boolean'],
            'youtubePlaylistUrl' => ['nullable', 'string', 'max:2048'],
            'youtubeDisplayMode' => ['required', 'in:cards,embed'],
        ]);

        if ($this->heroImageUpload) {
            if ($this->heroImagePath) {
                Storage::disk('public')->delete($this->heroImagePath);
            }

            $this->heroImagePath = ImageOptimizer::store($this->heroImageUpload, 'app/hero');
            $this->heroImageUpload = null;
        }

        SettingsStore::setApp([
            'welcome_title_text' => $this->titleText,
            'welcome_title_color' => strtoupper($this->titleColor),
            'welcome_name_animate' => $this->nameAnimate ? 'true' : 'false',
            'welcome_name_1_text' => $this->name1Text,
            'welcome_name_1_color' => strtoupper($this->name1Color),
            'welcome_name_2_text' => $this->name2Text,
            'welcome_name_2_color' => strtoupper($this->name2Color),
            'gallery_mode' => $this->galleryMode,
            'welcome_feed_source' => $this->welcomeFeedSource,
            'hero_image' => $this->heroImagePath,
            'hero_image_display' => $this->heroImageDisplay,
            'hero_button_1_enabled' => $this->heroButton1Enabled ? 'true' : 'false',
            'hero_button_1_label' => $this->heroButton1Label,
            'hero_button_1_link_type' => $this->heroButton1LinkType,
            'hero_button_1_link_value' => $this->heroButton1LinkValue,
            'hero_button_2_enabled' => $this->heroButton2Enabled ? 'true' : 'false',
            'hero_button_2_label' => $this->heroButton2Label,
            'hero_button_2_link_type' => $this->heroButton2LinkType,
            'hero_button_2_link_value' => $this->heroButton2LinkValue,
            'youtube_embed_enabled' => $this->youtubeEmbedEnabled ? 'true' : 'false',
            'youtube_playlist_url' => $this->youtubePlaylistUrl,
            'youtube_display_mode' => $this->youtubeDisplayMode,
        ]);

        $this->titleColor = strtoupper($this->titleColor);
        $this->name1Color = strtoupper($this->name1Color);
        $this->name2Color = strtoupper($this->name2Color);

        Flux::toast(variant: 'success', text: __('Landing Page settings updated.'));
    }

    public function heroImagePreviewUrl(): ?string
    {
        if ($this->heroImageUpload) {
            return $this->heroImageUpload->temporaryUrl();
        }

        if ($this->heroImagePath) {
            return Storage::disk('public')->url($this->heroImagePath);
        }

        return null;
    }

    public function youtubePreviewEmbedUrl(): ?string
    {
        return YoutubeEmbed::embedUrl($this->youtubePlaylistUrl);
    }

    public function updatedHeroButton1LinkType(): void
    {
        $this->heroButton1LinkValue = '';
    }

    public function updatedHeroButton2LinkType(): void
    {
        $this->heroButton2LinkValue = '';
    }

    /**
     * @return array<int, array{slug: string, title: string}>
     */
    public function customPages(): array
    {
        return CustomPage::query()
            ->orderBy('title')
            ->get(['slug', 'title'])
            ->map(fn (CustomPage $page): array => ['slug' => $page->slug, 'title' => $page->title])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function listPages(): array
    {
        return HeroLink::listPages();
    }
}; ?>

<section class="w-full">
    <div>
        <flux:heading level="1" size="xl">{{ __('Landing Page') }}</flux:heading>
        <flux:subheading>{{ __('Atur hero, konten, dan galeri yang tampil di halaman beranda.') }}</flux:subheading>
    </div>

    <div class="mt-5 w-full">
        @if (auth()->user()?->isSuperAdmin())
            <form wire:submit="save" class="space-y-6">
                <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <div>
                        <flux:heading size="sm">{{ __('Hero') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Kustomisasi judul dan nama yang tampil pada bagian hero beranda.') }}</flux:text>
                    </div>

                    <div class="grid gap-4 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                        <flux:input wire:model="titleText" :label="__('Judul Hero')" type="text" placeholder="Selamat Datang di Fansite" />
                        <div class="flex flex-col gap-3 rounded-lg border border-dashed border-zinc-300 p-3 dark:border-zinc-600">
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model.live="titleColor" class="h-11 w-14 shrink-0 cursor-pointer rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-800" aria-label="{{ __('Color picker') }} {{ __('Judul') }}" />
                                <span class="inline-flex size-9 shrink-0 rounded-full border border-zinc-200 shadow-sm dark:border-zinc-700" style="background-color: {{ $titleColor ?: '#FFFFFF' }}"></span>
                            </div>
                            <flux:input wire:model.live="titleColor" :label="__('Warna Judul')" type="text" placeholder="#FFFFFF" maxlength="7" />
                            @error('titleColor') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-4 rounded-lg border border-dashed border-zinc-300 p-4 dark:border-zinc-600">
                        <label class="inline-flex items-center gap-2 text-sm font-medium">
                            <input type="checkbox" wire:model.live="nameAnimate" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                            {{ __('Animasi pergantian nama') }}
                        </label>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Nama bergantian antar dua opsi di bawah (aktif pada profil versi JKT48). Kosongkan teks untuk memakai nama idol dari halaman About.') }}</flux:text>

                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ([1, 2] as $nameIndex)
                                @php
                                    $nameTextKey = "name{$nameIndex}Text";
                                    $nameColorKey = "name{$nameIndex}Color";
                                    $namePlaceholder = $nameIndex === 1 ? 'Cornelia Vanisa' : 'Oniel JKT48';
                                @endphp

                                <div class="space-y-3 rounded-lg border border-dashed border-zinc-300 p-3 dark:border-zinc-600">
                                    <flux:input wire:model="{{ $nameTextKey }}" :label="__('Nama :n', ['n' => $nameIndex])" type="text" placeholder="{{ $namePlaceholder }}" />
                                    <div class="flex items-center gap-3">
                                        <input type="color" wire:model.live="{{ $nameColorKey }}" class="h-11 w-14 shrink-0 cursor-pointer rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-800" aria-label="{{ __('Color picker') }} {{ __('Nama :n', ['n' => $nameIndex]) }}" />
                                        <flux:input wire:model.live="{{ $nameColorKey }}" :label="__('Warna Nama :n', ['n' => $nameIndex])" type="text" placeholder="#FDE047" maxlength="7" class="flex-1" />
                                    </div>
                                    @error($nameColorKey) <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid items-stretch gap-6 lg:grid-cols-2">
                    <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <div>
                            <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Galeri di Landing Page') }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pilih konten yang ditampilkan di halaman Landing Page.') }}</p>
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
                            <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Konten yang ditampilkan di Landing Page') }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pilih konten yang ditampilkan di Landing Page.') }}</p>
                        </div>

                        @foreach (['news' => 'News', 'blog' => 'Blog', 'magazines' => 'Majalah', 'trivia' => 'Trivia'] as $value => $label)
                            <label class="flex items-start gap-3">
                                <input type="radio" wire:model="welcomeFeedSource" value="{{ $value }}" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                                <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ __($label) }}</span>
                            </label>
                        @endforeach

                        @error('welcomeFeedSource') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <div>
                        <flux:heading size="sm">{{ __('Hero Image') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Gambar latar pada bagian hero beranda.') }}</flux:text>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            @if ($this->heroImagePreviewUrl())
                                <img src="{{ $this->heroImagePreviewUrl() }}" alt="Hero Image" class="h-auto w-full rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                            @else
                                <div class="flex aspect-video w-full items-center justify-center rounded-lg border border-dashed border-zinc-300 text-xs text-zinc-400 dark:border-zinc-600">
                                    <flux:icon.photo variant="micro" />
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col gap-2">
                                <flux:label>{{ __('Upload Gambar') }}</flux:label>
                                <input type="file" wire:model="heroImageUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                @error('heroImageUpload')
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-3 rounded-lg border border-dashed border-zinc-300 p-4 dark:border-zinc-600">
                                <div>
                                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Tampilan Hero Image') }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pilih cara gambar hero ditampilkan pada beranda.') }}</p>
                                </div>

                        @foreach ([
                            'fit' => ['label' => 'Fit', 'hint' => 'Memenuhi seluruh area hero, potong bila perlu.'],
                            'contain' => ['label' => 'Contain', 'hint' => 'Seluruh gambar terlihat, ada ruang kosong.'],
                            'adjustable' => ['label' => 'Adjustable Height', 'hint' => 'Tinggi hero mengikuti rasio gambar.'],
                            'original' => ['label' => 'Original', 'hint' => 'Ukuran asli gambar, tanpa diperbesar.'],
                        ] as $value => $option)
                                    <label class="flex items-start gap-3">
                                        <input type="radio" wire:model.live="heroImageDisplay" value="{{ $value }}" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                                        <span class="text-sm text-zinc-700 dark:text-zinc-200">
                                            <span class="font-medium">{{ $option['label'] }}</span>
                                            <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __($option['hint']) }}</span>
                                        </span>
                                    </label>
                                @endforeach

                                @error('heroImageDisplay') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <div>
                        <flux:heading size="sm">{{ __('Hero Buttons') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Kustomisasi label dan tautan dua tombol pada bagian hero beranda.') }}</flux:text>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ([1, 2] as $buttonIndex)
                        @php
                            $enabledKey = "heroButton{$buttonIndex}Enabled";
                            $labelKey = "heroButton{$buttonIndex}Label";
                            $typeKey = "heroButton{$buttonIndex}LinkType";
                            $valueKey = "heroButton{$buttonIndex}LinkValue";
                        @endphp

                        <div class="space-y-4 rounded-lg border border-dashed border-zinc-300 p-4 dark:border-zinc-600">
                            <label class="inline-flex items-center gap-2 text-sm font-medium">
                                <input type="checkbox" wire:model.live="{{ $enabledKey }}" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                                {{ __('Tampilkan Button :n', ['n' => $buttonIndex]) }}
                            </label>

                            @if ($this->{$enabledKey})
                                <flux:input wire:model="{{ $labelKey }}" :label="__('Label Button :n', ['n' => $buttonIndex])" type="text" />

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">{{ __('Tipe Tautan') }}</label>
                                    <select wire:model.live="{{ $typeKey }}" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                        <option value="url">{{ __('Direct Custom Link') }}</option>
                                        <option value="page">{{ __('Custom Page') }}</option>
                                        <option value="list">{{ __('List Page Bawaan') }}</option>
                                    </select>
                                </div>

                                @if ($this->{$typeKey} === 'url')
                                    <flux:input wire:model="{{ $valueKey }}" :label="__('URL / Anchor')" type="text" placeholder="https://... atau #about" />
                                @elseif ($this->{$typeKey} === 'page')
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">{{ __('Pilih Custom Page') }}</label>
                                        <select wire:model="{{ $valueKey }}" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                            <option value="">{{ __('-- Pilih Page --') }}</option>
                                            @foreach ($this->customPages() as $pageOption)
                                                <option value="{{ $pageOption['slug'] }}">{{ $pageOption['title'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">{{ __('Pilih List Page') }}</label>
                                        <select wire:model="{{ $valueKey }}" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                            <option value="">{{ __('-- Pilih Halaman --') }}</option>
                                            @foreach ($this->listPages() as $routeName => $routeLabel)
                                                <option value="{{ $routeName }}">{{ $routeLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                    </div>
                </div>

                <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Youtube Playlist') }}</flux:heading>

                    <label class="inline-flex items-center gap-2 text-sm font-medium">
                        <input type="checkbox" wire:model.live="youtubeEmbedEnabled" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                        {{ __('Tampilkan Youtube Playlist') }}
                    </label>

                    @if ($youtubeEmbedEnabled)
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-4">
                                <flux:input wire:model.blur="youtubePlaylistUrl" :label="__('Link Playlist Youtube')" type="text" placeholder="https://www.youtube.com/playlist?list=..." />

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">{{ __('Mode Tampilan') }}</label>
                                    <select wire:model.live="youtubeDisplayMode" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                        <option value="cards">{{ __('Cards (Carousel)') }}</option>
                                        <option value="embed">{{ __('Embed Playlist') }}</option>
                                    </select>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                        @if ($youtubeDisplayMode === 'cards')
                                            {{ __('Menampilkan 7 video terbaru dari RSS YouTube (3 tampil, dapat digeser). Tidak memerlukan API Key.') }}
                                        @else
                                            {{ __('Menampilkan player playlist langsung dari link yang diberikan.') }}
                                        @endif
                                    </flux:text>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <flux:label>{{ __('Preview Tampilan') }}</flux:label>

                                @if ($youtubeDisplayMode === 'embed')
                                    @if ($this->youtubePreviewEmbedUrl())
                                        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                                            <div class="relative aspect-video">
                                                <iframe
                                                    src="{{ $this->youtubePreviewEmbedUrl() }}"
                                                    title="Youtube Playlist Preview"
                                                    class="absolute inset-0 h-full w-full"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                    referrerpolicy="strict-origin-when-cross-origin"
                                                    allowfullscreen
                                                    loading="lazy"
                                                ></iframe>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex aspect-video w-full items-center justify-center rounded-xl border border-dashed border-zinc-300 text-xs text-zinc-400 dark:border-zinc-600">
                                            {{ __('Isi Link Playlist untuk melihat preview.') }}
                                        </div>
                                    @endif
                                @else
                                    <div class="grid grid-cols-3 gap-3">
                                        @foreach (range(1, 3) as $card)
                                            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                                                <div class="flex aspect-video w-full items-center justify-center bg-zinc-100 dark:bg-zinc-800">
                                                    <flux:icon.play-circle variant="solid" class="size-6 text-zinc-400" />
                                                </div>
                                                <div class="space-y-1.5 p-3">
                                                    <div class="h-2 w-full rounded bg-zinc-200 dark:bg-zinc-700"></div>
                                                    <div class="h-2 w-2/3 rounded bg-zinc-200 dark:bg-zinc-700"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Kartu menampilkan 3 video terbaru, dapat digeser untuk melihat sisanya.') }}</flux:text>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                </div>
            </form>
        @else
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pengaturan Landing Page hanya dapat diubah oleh Super Admin.') }}</p>
        @endif
    </div>
</section>

