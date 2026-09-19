<?php

use App\Models\CustomPage;
use App\Support\BrandPalette;
use App\Support\HeroLink;
use App\Support\SettingsStore;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Appearance settings')] class extends Component
{
    use WithFileUploads;

    public string $brandColor = '#6C7CE8';

    public string $brandColorSecondary = '#A5B4FC';

    public string $brandColorTertiary = '#FFD166';

    public string $appName = '';

    public string $descApp = '';

    public ?string $appLogoPath = null;

    public mixed $appLogoUpload = null;

    public ?string $heroImagePath = null;

    public mixed $heroImageUpload = null;

    public ?string $loginImagePath = null;

    public mixed $loginImageUpload = null;

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
        $settings = DB::table('app_settings')->pluck('value', 'key')->all();

        $this->brandColor = BrandPalette::normalize($settings['brand_color'] ?? null, BrandPalette::PRIMARY);
        $this->brandColorSecondary = BrandPalette::normalize($settings['brand_color_secondary'] ?? null, BrandPalette::SECONDARY);
        $this->brandColorTertiary = BrandPalette::normalize($settings['brand_color_tertiary'] ?? null, BrandPalette::TERTIARY);
        $this->appName = (string) ($settings['app_name'] ?? '');
        $this->descApp = (string) ($settings['desc_app'] ?? '');
        $this->appLogoPath = $settings['app_logo'] ?? null;
        $this->heroImagePath = $settings['hero_image'] ?? null;
        $this->loginImagePath = $settings['login_image'] ?? null;

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
            'brandColor' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'brandColorSecondary' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'brandColorTertiary' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'appName' => ['required', 'string', 'max:255'],
            'descApp' => ['nullable', 'string'],
            'appLogoUpload' => ['nullable', 'image', 'max:3072'],
            'heroImageUpload' => ['nullable', 'image', 'max:3072'],
            'loginImageUpload' => ['nullable', 'image', 'max:3072'],
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

        if ($this->appLogoUpload) {
            if ($this->appLogoPath) {
                Storage::disk('public')->delete($this->appLogoPath);
            }

            $this->appLogoPath = $this->appLogoUpload->store('app', 'public');
            $this->appLogoUpload = null;
        }

        if ($this->heroImageUpload) {
            if ($this->heroImagePath) {
                Storage::disk('public')->delete($this->heroImagePath);
            }

            $this->heroImagePath = $this->heroImageUpload->store('app/hero', 'public');
            $this->heroImageUpload = null;
        }

        if ($this->loginImageUpload) {
            if ($this->loginImagePath) {
                Storage::disk('public')->delete($this->loginImagePath);
            }

            $this->loginImagePath = $this->loginImageUpload->store('app/login', 'public');
            $this->loginImageUpload = null;
        }

        SettingsStore::setApp([
            'brand_color' => strtoupper($this->brandColor),
            'brand_color_secondary' => strtoupper($this->brandColorSecondary),
            'brand_color_tertiary' => strtoupper($this->brandColorTertiary),
            'app_name' => $this->appName,
            'sidebar_name' => $this->appName,
            'desc_app' => $this->descApp,
            'app_logo' => $this->appLogoPath,
            'hero_image' => $this->heroImagePath,
            'login_image' => $this->loginImagePath,
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

        $this->brandColor = strtoupper($this->brandColor);
        $this->brandColorSecondary = strtoupper($this->brandColorSecondary);
        $this->brandColorTertiary = strtoupper($this->brandColorTertiary);

        Flux::toast(variant: 'success', text: __('Appearance settings updated.'));
    }

    public function appLogoPreviewUrl(): ?string
    {
        if ($this->appLogoUpload) {
            return $this->appLogoUpload->temporaryUrl();
        }

        if ($this->appLogoPath) {
            return Storage::disk('public')->url($this->appLogoPath);
        }

        return null;
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

    public function loginImagePreviewUrl(): ?string
    {
        if ($this->loginImageUpload) {
            return $this->loginImageUpload->temporaryUrl();
        }

        if ($this->loginImagePath) {
            return Storage::disk('public')->url($this->loginImagePath);
        }

        return null;
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
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Kelola warna brand dan identitas situs')">
        @if (auth()->user()?->isSuperAdmin())
            <form wire:submit="save" class="space-y-6">
                <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <div>
                        <flux:heading size="sm">{{ __('Brand Color') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Tiga warna tema aplikasi (berlaku untuk light & dark mode). Gunakan color picker atau isi kode HEX.') }}</flux:text>
                    </div>

                    @php
                        $brandFields = [
                            ['key' => 'brandColor', 'label' => 'Primer', 'usage' => 'Tombol & tautan utama, border aktif, gradient hero, badge aktif.'],
                            ['key' => 'brandColorSecondary', 'label' => 'Sekunder', 'usage' => 'Aksen kedua: variasi gradient dan kartu sosial.'],
                            ['key' => 'brandColorTertiary', 'label' => 'Tersier', 'usage' => 'Sorotan: nama idol, tombol highlight, dan badge.'],
                        ];
                    @endphp

                    @foreach ($brandFields as $field)
                        <div class="space-y-2 rounded-lg border border-dashed border-zinc-300 p-3 dark:border-zinc-600">
                            <div class="flex flex-wrap items-center gap-3">
                                <input
                                    type="color"
                                    wire:model.live="{{ $field['key'] }}"
                                    class="h-11 w-14 cursor-pointer rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-800"
                                    aria-label="{{ __('Color picker') }} {{ $field['label'] }}"
                                />
                                <flux:input wire:model.live="{{ $field['key'] }}" :label="__($field['label'])" type="text" placeholder="#6C7CE8" maxlength="7" class="w-40" />
                                <span class="inline-flex size-9 rounded-full border border-zinc-200 shadow-sm dark:border-zinc-700" style="background-color: {{ $this->{$field['key']} ?: '#6C7CE8' }}"></span>
                            </div>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $field['usage'] }}</flux:text>
                            @error($field['key'])
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('App Identity') }}</flux:heading>

                    <flux:input wire:model="appName" :label="__('App Name / Sidebar Name')" type="text" required />
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Nilai ini dipakai sebagai App Name sekaligus Sidebar Name.') }}</flux:text>

                    <flux:textarea wire:model="descApp" :label="__('Desc App')" rows="4" />

                    <div class="space-y-2">
                        <flux:label>{{ __('App Logo') }}</flux:label>
                        <input type="file" wire:model="appLogoUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                        @error('appLogoUpload')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        @if ($this->appLogoPreviewUrl())
                            <img src="{{ $this->appLogoPreviewUrl() }}" alt="App Logo" class="mt-2 h-20 w-auto rounded-lg border border-zinc-200 object-contain dark:border-zinc-700">
                        @endif
                    </div>

                    <div class="space-y-2">
                        <flux:label>{{ __('Hero Image') }}</flux:label>
                        <input type="file" wire:model="heroImageUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                        @error('heroImageUpload')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        @if ($this->heroImagePreviewUrl())
                            <img src="{{ $this->heroImagePreviewUrl() }}" alt="Hero Image" class="mt-2 h-full w-full rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                        @endif
                    </div>

                    <div class="space-y-2">
                        <flux:label>{{ __('Login Image') }}</flux:label>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Ditampilkan sebagai panel gambar pada halaman login.') }}</flux:text>
                        <input type="file" wire:model="loginImageUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                        @error('loginImageUpload')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        @if ($this->loginImagePreviewUrl())
                            <img src="{{ $this->loginImagePreviewUrl() }}" alt="Login Image" class="mt-2 aspect-video w-full rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                        @endif
                    </div>
                </div>

                <div class="space-y-6 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <div>
                        <flux:heading size="sm">{{ __('Hero Buttons') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Kustomisasi label dan tautan dua tombol pada bagian hero beranda.') }}</flux:text>
                    </div>

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

                <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Youtube Playlist') }}</flux:heading>

                    <label class="inline-flex items-center gap-2 text-sm font-medium">
                        <input type="checkbox" wire:model.live="youtubeEmbedEnabled" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                        {{ __('Tampilkan Youtube Playlist') }}
                    </label>

                    @if ($youtubeEmbedEnabled)
                        <flux:input wire:model="youtubePlaylistUrl" :label="__('Link Playlist Youtube')" type="text" placeholder="https://www.youtube.com/playlist?list=..." />

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
                    @endif
                </div>

                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                </div>
            </form>
        @else
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pengaturan tampilan hanya dapat diubah oleh Super Admin.') }}</p>
        @endif
    </x-pages::settings.layout>
</section>
