<?php

use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('About settings')] class extends Component {
    use WithFileUploads;

    public string $activeTab = 'idol';

    public string $idolName = '';
    public ?string $idolPhotoPath = null;
    public mixed $idolPhotoUpload = null;
    public string $idolDescription = '';
    public string $idolAchievements = '';
    public string $idolDiscography = '';
    public string $idolJikoshoukai = '';
    public ?string $idolBirthDate = null;
    public string $idolBirthPlace = '';
    public string $idolBloodType = '';
    public string $idolHoroscope = '';
    public string $idolInstagram = '';
    public string $idolTiktok = '';
    public string $idolTwitter = '';
    public bool $idolShowOnWelcome = false;

    public string $fanbaseName = '';
    public ?string $fanbaseLogoPath = null;
    public mixed $fanbaseLogoUpload = null;
    public string $fanbaseDescription = '';
    public string $fanbaseActivities = '';
    public array $fanbaseGalleryPaths = [];
    public array $fanbaseGalleryUploads = [];
    public bool $fanbaseCtaEnabled = false;
    public ?string $fanbaseCtaBackgroundPath = null;
    public mixed $fanbaseCtaBackgroundUpload = null;
    public string $fanbaseCtaTitle = '';
    public string $fanbaseCtaButton1Text = '';
    public string $fanbaseCtaButton1Link = '';
    public string $fanbaseCtaButton2Text = '';
    public string $fanbaseCtaButton2Link = '';

    public array $bloodTypes = ['A', 'B', 'AB', 'O'];

    public array $zodiacs = [
        'Aries',
        'Taurus',
        'Gemini',
        'Cancer',
        'Leo',
        'Virgo',
        'Libra',
        'Scorpio',
        'Sagittarius',
        'Capricorn',
        'Aquarius',
        'Pisces',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $settings = DB::table('about_settings')->pluck('value', 'key')->all();

        $this->idolName = (string) ($settings['idol_name'] ?? '');
        $this->idolPhotoPath = $settings['idol_photo'] ?? null;
        $this->idolDescription = (string) ($settings['idol_description'] ?? '');
        $this->idolAchievements = (string) ($settings['idol_achievements'] ?? '');
        $this->idolDiscography = (string) ($settings['idol_discography'] ?? '');
        $this->idolJikoshoukai = (string) ($settings['idol_jikoshoukai'] ?? '');
        $this->idolBirthDate = ($settings['idol_birth_date'] ?? null) ?: null;
        $this->idolBirthPlace = (string) ($settings['idol_birth_place'] ?? '');
        $this->idolBloodType = (string) ($settings['idol_blood_type'] ?? '');
        $this->idolHoroscope = (string) ($settings['idol_horoscope'] ?? '');
        $this->idolInstagram = (string) ($settings['idol_social_media_instagram'] ?? '');
        $this->idolTiktok = (string) ($settings['idol_social_media_tiktok'] ?? '');
        $this->idolTwitter = (string) ($settings['idol_social_media_twitter'] ?? '');
        $this->idolShowOnWelcome = filter_var($settings['idol_show_on_welcome'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $this->fanbaseName = (string) ($settings['fanbase_name'] ?? '');
        $this->fanbaseLogoPath = $settings['fanbase_logo'] ?? null;
        $this->fanbaseDescription = (string) ($settings['fanbase_description'] ?? '');
        $this->fanbaseActivities = (string) ($settings['fanbase_activities'] ?? '');

        $gallery = json_decode((string) ($settings['fanbase_gallery'] ?? '[]'), true);
        $this->fanbaseGalleryPaths = is_array($gallery) ? array_values(array_filter($gallery)) : [];

        $this->fanbaseCtaEnabled = filter_var($settings['fanbase_cta_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->fanbaseCtaBackgroundPath = $settings['fanbase_cta_background'] ?? null;
        $this->fanbaseCtaTitle = (string) ($settings['fanbase_cta_title'] ?? '');
        $this->fanbaseCtaButton1Text = (string) ($settings['fanbase_cta_button1_text'] ?? '');
        $this->fanbaseCtaButton1Link = (string) ($settings['fanbase_cta_button1_link'] ?? '');
        $this->fanbaseCtaButton2Text = (string) ($settings['fanbase_cta_button2_text'] ?? '');
        $this->fanbaseCtaButton2Link = (string) ($settings['fanbase_cta_button2_link'] ?? '');
    }

    public function setActiveTab(string $tab): void
    {
        if (! in_array($tab, ['idol', 'fansite'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function saveIdol(): void
    {
        $this->validate([
            'idolName' => ['required', 'string', 'max:255'],
            'idolPhotoUpload' => ['nullable', 'image', 'max:3072'],
            'idolDescription' => ['nullable', 'string'],
            'idolAchievements' => ['nullable', 'string'],
            'idolDiscography' => ['nullable', 'string'],
            'idolJikoshoukai' => ['nullable', 'string'],
            'idolBirthDate' => ['nullable', 'date'],
            'idolBirthPlace' => ['nullable', 'string', 'max:255'],
            'idolBloodType' => ['nullable', 'in:A,B,AB,O'],
            'idolHoroscope' => ['nullable', 'in:Aries,Taurus,Gemini,Cancer,Leo,Virgo,Libra,Scorpio,Sagittarius,Capricorn,Aquarius,Pisces'],
            'idolInstagram' => ['nullable', 'url', 'max:255'],
            'idolTiktok' => ['nullable', 'url', 'max:255'],
            'idolTwitter' => ['nullable', 'url', 'max:255'],
            'idolShowOnWelcome' => ['boolean'],
        ]);

        if ($this->idolPhotoUpload) {
            if ($this->idolPhotoPath) {
                Storage::disk('public')->delete($this->idolPhotoPath);
            }

            $this->idolPhotoPath = $this->idolPhotoUpload->store('about/idol', 'public');
            $this->idolPhotoUpload = null;
        }

        $this->upsertSettings([
            'idol_name' => $this->idolName,
            'idol_slug' => Str::slug($this->idolName),
            'idol_photo' => $this->idolPhotoPath,
            'idol_description' => $this->idolDescription,
            'idol_achievements' => $this->idolAchievements,
            'idol_discography' => $this->idolDiscography,
            'idol_jikoshoukai' => $this->idolJikoshoukai,
            'idol_birth_date' => $this->idolBirthDate,
            'idol_birth_place' => $this->idolBirthPlace,
            'idol_blood_type' => $this->idolBloodType,
            'idol_horoscope' => $this->idolHoroscope,
            'idol_social_media_instagram' => $this->idolInstagram,
            'idol_social_media_tiktok' => $this->idolTiktok,
            'idol_social_media_twitter' => $this->idolTwitter,
            'idol_show_on_welcome' => $this->idolShowOnWelcome ? 'true' : 'false',
        ]);

        Flux::toast(variant: 'success', text: __('Idol information updated.'));
    }

    public function saveFansite(): void
    {
        $this->validate([
            'fanbaseName' => ['required', 'string', 'max:255'],
            'fanbaseLogoUpload' => ['nullable', 'image', 'max:3072'],
            'fanbaseDescription' => ['nullable', 'string'],
            'fanbaseActivities' => ['nullable', 'string'],
            'fanbaseGalleryUploads' => ['nullable', 'array', 'max:5'],
            'fanbaseGalleryUploads.*' => ['image', 'max:3072'],
            'fanbaseCtaEnabled' => ['boolean'],
            'fanbaseCtaBackgroundUpload' => ['nullable', 'image', 'max:3072'],
            'fanbaseCtaTitle' => ['nullable', 'string', 'max:255'],
            'fanbaseCtaButton1Text' => ['nullable', 'string', 'max:255'],
            'fanbaseCtaButton1Link' => ['nullable', 'url', 'max:255'],
            'fanbaseCtaButton2Text' => ['nullable', 'string', 'max:255'],
            'fanbaseCtaButton2Link' => ['nullable', 'url', 'max:255'],
        ]);

        if ($this->fanbaseLogoUpload) {
            if ($this->fanbaseLogoPath) {
                Storage::disk('public')->delete($this->fanbaseLogoPath);
            }

            $this->fanbaseLogoPath = $this->fanbaseLogoUpload->store('about/fansite', 'public');
            $this->fanbaseLogoUpload = null;
        }

        if (! empty($this->fanbaseGalleryUploads)) {
            foreach ($this->fanbaseGalleryPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            $this->fanbaseGalleryPaths = collect($this->fanbaseGalleryUploads)
                ->take(5)
                ->map(fn ($file) => $file->store('about/fansite/gallery', 'public'))
                ->values()
                ->all();

            $this->fanbaseGalleryUploads = [];
        }

        if ($this->fanbaseCtaBackgroundUpload) {
            if ($this->fanbaseCtaBackgroundPath) {
                Storage::disk('public')->delete($this->fanbaseCtaBackgroundPath);
            }

            $this->fanbaseCtaBackgroundPath = $this->fanbaseCtaBackgroundUpload->store('about/fansite/cta', 'public');
            $this->fanbaseCtaBackgroundUpload = null;
        }

        $this->upsertSettings([
            'fanbase_name' => $this->fanbaseName,
            'fanbase_slug' => Str::slug($this->fanbaseName),
            'fanbase_logo' => $this->fanbaseLogoPath,
            'fanbase_description' => $this->fanbaseDescription,
            'fanbase_activities' => $this->fanbaseActivities,
            'fanbase_gallery' => json_encode($this->fanbaseGalleryPaths),
            'fanbase_cta_enabled' => $this->fanbaseCtaEnabled ? 'true' : 'false',
            'fanbase_cta_background' => $this->fanbaseCtaEnabled ? $this->fanbaseCtaBackgroundPath : null,
            'fanbase_cta_title' => $this->fanbaseCtaEnabled ? $this->fanbaseCtaTitle : null,
            'fanbase_cta_button1_text' => $this->fanbaseCtaEnabled ? $this->fanbaseCtaButton1Text : null,
            'fanbase_cta_button1_link' => $this->fanbaseCtaEnabled ? $this->fanbaseCtaButton1Link : null,
            'fanbase_cta_button2_text' => $this->fanbaseCtaEnabled ? $this->fanbaseCtaButton2Text : null,
            'fanbase_cta_button2_link' => $this->fanbaseCtaEnabled ? $this->fanbaseCtaButton2Link : null,
        ]);

        Flux::toast(variant: 'success', text: __('Fansite information updated.'));
    }

    public function idolPhotoPreviewUrl(): ?string
    {
        if ($this->idolPhotoUpload) {
            return $this->idolPhotoUpload->temporaryUrl();
        }

        if ($this->idolPhotoPath) {
            return Storage::disk('public')->url($this->idolPhotoPath);
        }

        return null;
    }

    public function fanbaseLogoPreviewUrl(): ?string
    {
        if ($this->fanbaseLogoUpload) {
            return $this->fanbaseLogoUpload->temporaryUrl();
        }

        if ($this->fanbaseLogoPath) {
            return Storage::disk('public')->url($this->fanbaseLogoPath);
        }

        return null;
    }

    public function fanbaseCtaBackgroundPreviewUrl(): ?string
    {
        if ($this->fanbaseCtaBackgroundUpload) {
            return $this->fanbaseCtaBackgroundUpload->temporaryUrl();
        }

        if ($this->fanbaseCtaBackgroundPath) {
            return Storage::disk('public')->url($this->fanbaseCtaBackgroundPath);
        }

        return null;
    }

    public function galleryPreviewUrls(): array
    {
        $existing = collect($this->fanbaseGalleryPaths)
            ->map(fn ($path) => Storage::disk('public')->url($path));

        $uploads = collect($this->fanbaseGalleryUploads)
            ->map(fn ($file) => $file->temporaryUrl());

        return $existing->merge($uploads)->take(5)->values()->all();
    }

    private function upsertSettings(array $settings): void
    {
        $now = now();
        $rows = collect($settings)
            ->map(fn ($value, $key) => [
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        DB::table('about_settings')->upsert($rows, ['key'], ['value', 'updated_at']);

        Cache::forget('about_settings');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('About settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('About')" :subheading="__('Kelola informasi Idol dan Fansite')" :maxWidthClass="'max-w-4xl'">
        <div class="space-y-6">
            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-zinc-200 p-1 dark:border-zinc-700">
                <flux:button
                    type="button"
                    size="sm"
                    :variant="$activeTab === 'idol' ? 'primary' : 'ghost'"
                    wire:click="setActiveTab('idol')"
                >
                    Idol Information
                </flux:button>

                <flux:button
                    type="button"
                    size="sm"
                    :variant="$activeTab === 'fansite' ? 'primary' : 'ghost'"
                    wire:click="setActiveTab('fansite')"
                >
                    Fansite Information
                </flux:button>
            </div>

            @if ($activeTab === 'idol')
                <form wire:submit="saveIdol" class="space-y-8">
                    <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">Basic Information</flux:heading>

                        <flux:input wire:model="idolName" :label="__('Nama Oshimen (Idol Name)')" type="text" required />

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Upload Idol Photo</label>
                            <input type="file" wire:model="idolPhotoUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                            @error('idolPhotoUpload')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror

                            @if ($this->idolPhotoPreviewUrl())
                                <div class="mt-2 h-40 w-40 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <img src="{{ $this->idolPhotoPreviewUrl() }}" alt="Idol photo preview" class="h-full w-full object-cover object-center">
                                </div>
                            @endif
                        </div>

                        <flux:textarea wire:model="idolDescription" :label="__('Tentang Oshimen')" rows="4" />
                    </div>

                    <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">Achievements & Discography</flux:heading>

                        <flux:textarea wire:model="idolAchievements" :label="__('Achievements')" rows="4" />
                        <flux:textarea wire:model="idolDiscography" :label="__('Discography')" rows="4" />
                    </div>

                    <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">Profile Details (Biodata)</flux:heading>

                        <flux:textarea wire:model="idolJikoshoukai" :label="__('Jikoshoukai/Salam Perkenalan')" rows="3" />
                        <flux:input wire:model="idolBirthDate" :label="__('Tanggal Lahir')" type="date" />
                        <flux:input wire:model="idolBirthPlace" :label="__('Tempat Lahir')" type="text" />

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Golongan Darah</label>
                                <select wire:model="idolBloodType" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                    <option value="">Pilih golongan darah</option>
                                    @foreach ($bloodTypes as $bloodType)
                                        <option value="{{ $bloodType }}">{{ $bloodType }}</option>
                                    @endforeach
                                </select>
                                @error('idolBloodType')
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Zodiak</label>
                                <select wire:model="idolHoroscope" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                    <option value="">Pilih zodiak</option>
                                    @foreach ($zodiacs as $zodiac)
                                        <option value="{{ $zodiac }}">{{ $zodiac }}</option>
                                    @endforeach
                                </select>
                                @error('idolHoroscope')
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">Social Media Links</flux:heading>

                        <flux:input wire:model="idolInstagram" :label="__('URL Instagram')" type="url" placeholder="https://instagram.com/..." />
                        <flux:input wire:model="idolTiktok" :label="__('URL Tiktok')" type="url" placeholder="https://tiktok.com/@..." />
                        <flux:input wire:model="idolTwitter" :label="__('URL Twitter/X')" type="url" placeholder="https://x.com/..." />

                        <label class="inline-flex items-center gap-2 text-sm font-medium">
                            <input type="checkbox" wire:model="idolShowOnWelcome" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                            Tampilkan Informasi Oshimen di Homepage
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary">Simpan Idol Information</flux:button>
                    </div>
                </form>
            @endif

            @if ($activeTab === 'fansite')
                <form wire:submit="saveFansite" class="space-y-8">
                    <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">Basic Information</flux:heading>

                        <flux:input wire:model="fanbaseName" :label="__('Nama Fanbase')" type="text" required />

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Upload Logo Fanbase</label>
                            <input type="file" wire:model="fanbaseLogoUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                            @error('fanbaseLogoUpload')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror

                            @if ($this->fanbaseLogoPreviewUrl())
                                <div class="mt-2 h-40 w-40 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <img src="{{ $this->fanbaseLogoPreviewUrl() }}" alt="Fanbase logo preview" class="h-full w-full object-cover object-center">
                                </div>
                            @endif
                        </div>

                        <flux:textarea wire:model="fanbaseDescription" :label="__('Tentang Fanbase')" rows="4" />
                    </div>

                    <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">Activities and Gallery</flux:heading>

                        <flux:textarea wire:model="fanbaseActivities" :label="__('Activities')" rows="4" />

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Gallery (maksimal 5 gambar)</label>
                            <input type="file" wire:model="fanbaseGalleryUploads" accept="image/*" multiple class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                            @error('fanbaseGalleryUploads')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('fanbaseGalleryUploads.*')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror

                            @if (count($this->galleryPreviewUrls()) > 0)
                                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                                    @foreach ($this->galleryPreviewUrls() as $index => $previewUrl)
                                        <div wire:key="gallery-preview-{{ $index }}" class="aspect-square overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                                            <img src="{{ $previewUrl }}" alt="Gallery preview {{ $index + 1 }}" class="h-full w-full object-cover object-center">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">Call-To-Action</flux:heading>

                        <label class="inline-flex items-center gap-2 text-sm font-medium">
                            <input type="checkbox" wire:model="fanbaseCtaEnabled" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                            Enable CTA Action
                        </label>

                        @if ($fanbaseCtaEnabled)
                            <div class="space-y-4 rounded-lg border border-dashed border-zinc-300 p-4 dark:border-zinc-600">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Background Image</label>
                                    <input type="file" wire:model="fanbaseCtaBackgroundUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                    @error('fanbaseCtaBackgroundUpload')
                                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror

                                    @if ($this->fanbaseCtaBackgroundPreviewUrl())
                                        <div class="mt-2 aspect-[16/6] overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                                            <img src="{{ $this->fanbaseCtaBackgroundPreviewUrl() }}" alt="CTA background preview" class="h-full w-full object-cover object-center">
                                        </div>
                                    @endif
                                </div>

                                <flux:input wire:model="fanbaseCtaTitle" :label="__('CTA Title')" type="text" />

                                <div class="grid gap-4 md:grid-cols-2">
                                    <flux:input wire:model="fanbaseCtaButton1Text" :label="__('Button 1 Text')" type="text" />
                                    <flux:input wire:model="fanbaseCtaButton1Link" :label="__('Button 1 Link')" type="url" placeholder="https://..." />
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <flux:input wire:model="fanbaseCtaButton2Text" :label="__('Button 2 Text')" type="text" />
                                    <flux:input wire:model="fanbaseCtaButton2Link" :label="__('Button 2 Link')" type="url" placeholder="https://..." />
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary">Simpan Fansite Information</flux:button>
                    </div>
                </form>
            @endif
        </div>
    </x-pages::settings.layout>
</section>
