<?php

use App\Models\CustomPage;
use App\Support\SettingBag;
use App\Support\SettingsStore;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('About Settings')] class extends Component
{
    use WithFileUploads;

    public string $activeTab = 'idol';

    public string $idolName = '';

    public string $idolShortname = '';

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

    public string $idolProfileVersion = 'jkt48';

    public bool $kabeshaEnabled = true;

    public string $kabeshaDefaultTitle = '';

    /** @var array<int, array{photo: string|null, title: string, duration_from: string|null, duration_to: string|null}> */
    public array $kabeshaItems = [];

    public array $kabeshaPhotoUploads = [];

    public string $fanbaseName = '';

    public ?string $fanbaseLogoPath = null;

    public mixed $fanbaseLogoUpload = null;

    public string $fanbaseDescription = '';

    public string $fanbaseStructure = '';

    public bool $fanbaseStructureEnabled = true;

    public string $fanbaseActivities = '';

    public bool $fanbaseActivitiesEnabled = true;

    /** @var array<int, array{photo: string|null, caption: string}> */
    public array $fanbaseGalleryItems = [];

    public array $fanbaseGalleryUploads = [];

    public bool $fanbaseHistoryEnabled = false;

    public string $fanbaseHistorySource = 'default';

    public ?int $fanbaseHistoryCustomPageId = null;

    /** @var array<int, array{photo: string|null, description: string}> */
    public array $fanbaseHistoryItems = [];

    public array $fanbaseHistoryUploads = [];

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
        $this->idolShortname = (string) ($settings['idol_shortname'] ?? '');
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

        $versionValue = (string) ($settings['idol_profile_version'] ?? 'jkt48');
        $this->idolProfileVersion = in_array($versionValue, ['jkt48', 'general'], true) ? $versionValue : 'jkt48';
        $this->kabeshaEnabled = filter_var($settings['kabesha_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->kabeshaDefaultTitle = (string) ($settings['kabesha_default_title'] ?? '');

        $this->kabeshaItems = SettingBag::kabeshaItems($settings);

        $this->fanbaseName = (string) ($settings['fanbase_name'] ?? '');
        $this->fanbaseLogoPath = $settings['fanbase_logo'] ?? null;
        $this->fanbaseDescription = (string) ($settings['fanbase_description'] ?? '');
        $this->fanbaseStructure = (string) ($settings['fanbase_structure'] ?? '');
        $this->fanbaseStructureEnabled = filter_var($settings['fanbase_structure_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->fanbaseActivities = (string) ($settings['fanbase_activities'] ?? '');
        $this->fanbaseActivitiesEnabled = filter_var($settings['fanbase_activities_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);

        $this->fanbaseGalleryItems = SettingBag::fanbaseGalleryItems($settings);

        $this->fanbaseHistoryEnabled = filter_var($settings['fanbase_history_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $historySource = (string) ($settings['fanbase_history_source'] ?? 'default');
        $this->fanbaseHistorySource = in_array($historySource, ['custom', 'default'], true) ? $historySource : 'default';
        $customPageId = (int) ($settings['fanbase_history_custom_page_id'] ?? 0);
        $this->fanbaseHistoryCustomPageId = $customPageId > 0 ? $customPageId : null;
        $this->fanbaseHistoryItems = SettingBag::fanbaseHistoryItems($settings);

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
            'idolShortname' => ['nullable', 'string', 'max:100'],
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
            'idolProfileVersion' => ['required', 'in:jkt48,general'],
            'kabeshaEnabled' => ['boolean'],
            'kabeshaDefaultTitle' => ['nullable', 'string', 'max:255'],
            'kabeshaItems' => ['nullable', 'array'],
            'kabeshaItems.*.title' => ['nullable', 'string', 'max:255'],
            'kabeshaItems.*.duration_from' => ['nullable', 'date'],
            'kabeshaItems.*.duration_to' => ['nullable', 'date', 'after_or_equal:kabeshaItems.*.duration_from'],
            'kabeshaPhotoUploads' => ['nullable', 'array'],
            'kabeshaPhotoUploads.*' => ['image', 'max:3072'],
        ]);

        if ($this->idolPhotoUpload) {
            if ($this->idolPhotoPath) {
                Storage::disk('public')->delete($this->idolPhotoPath);
            }

            $this->idolPhotoPath = $this->idolPhotoUpload->store('about/idol', 'public');
            $this->idolPhotoUpload = null;
        }

        foreach ($this->kabeshaPhotoUploads as $kabeshaUpload) {
            $this->kabeshaItems[] = [
                'photo' => $kabeshaUpload->store('about/kabesha', 'public'),
                'title' => $this->kabeshaDefaultTitle,
                'duration_from' => null,
                'duration_to' => null,
            ];
        }

        $this->kabeshaPhotoUploads = [];
        $this->kabeshaItems = array_values($this->kabeshaItems);

        $this->upsertSettings([
            'idol_name' => $this->idolName,
            'idol_shortname' => $this->idolShortname,
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
            'idol_profile_version' => $this->idolProfileVersion,
            'kabesha_enabled' => $this->kabeshaEnabled ? 'true' : 'false',
            'kabesha_default_title' => $this->kabeshaDefaultTitle,
            'kabesha_items' => json_encode(array_values($this->kabeshaItems)),
        ]);

        Flux::toast(variant: 'success', text: __('Idol information updated.'));
    }

    public function saveFansite(): void
    {
        $this->validate([
            'fanbaseName' => ['required', 'string', 'max:255'],
            'fanbaseLogoUpload' => ['nullable', 'image', 'max:3072'],
            'fanbaseDescription' => ['nullable', 'string'],
            'fanbaseStructure' => ['nullable', 'string'],
            'fanbaseStructureEnabled' => ['boolean'],
            'fanbaseActivities' => ['nullable', 'string'],
            'fanbaseActivitiesEnabled' => ['boolean'],
            'fanbaseGalleryItems' => ['nullable', 'array', 'max:20'],
            'fanbaseGalleryItems.*.caption' => ['nullable', 'string', 'max:255'],
            'fanbaseGalleryUploads' => ['nullable', 'array', 'max:20'],
            'fanbaseGalleryUploads.*' => ['image', 'max:3072'],
            'fanbaseHistoryEnabled' => ['boolean'],
            'fanbaseHistorySource' => ['required', 'in:custom,default'],
            'fanbaseHistoryCustomPageId' => ['nullable', 'integer', 'exists:custom_pages,id'],
            'fanbaseHistoryItems' => ['nullable', 'array', 'max:20'],
            'fanbaseHistoryItems.*.description' => ['nullable', 'string', 'max:255'],
            'fanbaseHistoryUploads' => ['nullable', 'array', 'max:20'],
            'fanbaseHistoryUploads.*' => ['image', 'max:3072'],
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

        $remaining = max(0, 20 - count($this->fanbaseGalleryItems));

        foreach (collect($this->fanbaseGalleryUploads)->take($remaining) as $upload) {
            $this->fanbaseGalleryItems[] = [
                'photo' => $upload->store('about/fansite/gallery', 'public'),
                'caption' => '',
            ];
        }

        $this->fanbaseGalleryUploads = [];
        $this->fanbaseGalleryItems = array_values($this->fanbaseGalleryItems);

        $remainingHistory = max(0, 20 - count($this->fanbaseHistoryItems));

        foreach (collect($this->fanbaseHistoryUploads)->take($remainingHistory) as $historyUpload) {
            $this->fanbaseHistoryItems[] = [
                'photo' => $historyUpload->store('about/fansite/history', 'public'),
                'description' => '',
            ];
        }

        $this->fanbaseHistoryUploads = [];
        $this->fanbaseHistoryItems = array_values($this->fanbaseHistoryItems);

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
            'fanbase_structure' => $this->fanbaseStructure,
            'fanbase_structure_enabled' => $this->fanbaseStructureEnabled ? 'true' : 'false',
            'fanbase_activities' => $this->fanbaseActivities,
            'fanbase_activities_enabled' => $this->fanbaseActivitiesEnabled ? 'true' : 'false',
            'fanbase_gallery_items' => json_encode(array_values($this->fanbaseGalleryItems)),
            'fanbase_gallery' => json_encode(array_values(array_filter(array_column($this->fanbaseGalleryItems, 'photo')))),
            'fanbase_history_enabled' => $this->fanbaseHistoryEnabled ? 'true' : 'false',
            'fanbase_history_source' => $this->fanbaseHistorySource,
            'fanbase_history_custom_page_id' => ($this->fanbaseHistoryEnabled && $this->fanbaseHistorySource === 'custom') ? $this->fanbaseHistoryCustomPageId : null,
            'fanbase_history_items' => json_encode(array_values($this->fanbaseHistoryItems)),
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

    /**
     * @return array<int, array{photo: string|null, title: string, duration_from: string|null, duration_to: string|null}>
     */
    public function kabeshaItemsWithPreview(): array
    {
        return array_map(fn (array $item): array => [
            'photo' => $item['photo'],
            'preview' => filled($item['photo']) ? Storage::disk('public')->url($item['photo']) : null,
            'title' => $item['title'],
            'duration_from' => $item['duration_from'],
            'duration_to' => $item['duration_to'],
        ], $this->kabeshaItems);
    }

    public function removeKabeshaItem(int $index): void
    {
        if (! isset($this->kabeshaItems[$index])) {
            return;
        }

        $photo = $this->kabeshaItems[$index]['photo'] ?? null;

        if (filled($photo)) {
            Storage::disk('public')->delete($photo);
        }

        unset($this->kabeshaItems[$index]);
        $this->kabeshaItems = array_values($this->kabeshaItems);

        $this->persistKabeshaItems();

        Flux::toast(variant: 'success', text: __('Foto Kabesha dihapus.'));
    }

    /**
     * Reorder Kabesha photos from a drag-and-drop payload (list of previous indexes).
     *
     * @param  array<int, int|string>  $order
     */
    public function reorderKabeshaItems(array $order): void
    {
        $items = array_values($this->kabeshaItems);
        $order = array_map('intval', array_values($order));
        $reordered = [];

        foreach ($order as $index) {
            if (isset($items[$index])) {
                $reordered[] = $items[$index];
            }
        }

        foreach (array_keys($items) as $index) {
            if (! in_array($index, $order, true)) {
                $reordered[] = $items[$index];
            }
        }

        if ($reordered === [] || count($reordered) !== count($items)) {
            return;
        }

        $this->kabeshaItems = array_values($reordered);

        $this->persistKabeshaItems();
    }

    private function persistKabeshaItems(): void
    {
        $this->upsertSettings([
            'kabesha_items' => json_encode(array_values($this->kabeshaItems)),
        ]);
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

    /**
     * @return array<int, array{photo: string|null, preview: string|null, caption: string}>
     */
    public function fanbaseGalleryItemsWithPreview(): array
    {
        return array_map(fn (array $item): array => [
            'photo' => $item['photo'],
            'preview' => filled($item['photo']) ? Storage::disk('public')->url($item['photo']) : null,
            'caption' => $item['caption'],
        ], $this->fanbaseGalleryItems);
    }

    public function removeFanbaseGalleryItem(int $index): void
    {
        if (! isset($this->fanbaseGalleryItems[$index])) {
            return;
        }

        $photo = $this->fanbaseGalleryItems[$index]['photo'] ?? null;

        if (filled($photo)) {
            Storage::disk('public')->delete($photo);
        }

        unset($this->fanbaseGalleryItems[$index]);
        $this->fanbaseGalleryItems = array_values($this->fanbaseGalleryItems);

        $this->persistFanbaseGalleryItems();

        Flux::toast(variant: 'success', text: __('Foto galeri dihapus.'));
    }

    private function persistFanbaseGalleryItems(): void
    {
        SettingsStore::setAbout([
            'fanbase_gallery_items' => json_encode(array_values($this->fanbaseGalleryItems)),
            'fanbase_gallery' => json_encode(array_values(array_filter(array_column($this->fanbaseGalleryItems, 'photo')))),
        ]);
    }

    /**
     * @return array<int, array{photo: string|null, preview: string|null, description: string}>
     */
    public function fanbaseHistoryItemsWithPreview(): array
    {
        return array_map(fn (array $item): array => [
            'photo' => $item['photo'],
            'preview' => filled($item['photo']) ? Storage::disk('public')->url($item['photo']) : null,
            'description' => $item['description'],
        ], $this->fanbaseHistoryItems);
    }

    public function removeFanbaseHistoryItem(int $index): void
    {
        if (! isset($this->fanbaseHistoryItems[$index])) {
            return;
        }

        $photo = $this->fanbaseHistoryItems[$index]['photo'] ?? null;

        if (filled($photo)) {
            Storage::disk('public')->delete($photo);
        }

        unset($this->fanbaseHistoryItems[$index]);
        $this->fanbaseHistoryItems = array_values($this->fanbaseHistoryItems);

        $this->persistFanbaseHistoryItems();

        Flux::toast(variant: 'success', text: __('Foto sejarah dihapus.'));
    }

    private function persistFanbaseHistoryItems(): void
    {
        SettingsStore::setAbout([
            'fanbase_history_items' => json_encode(array_values($this->fanbaseHistoryItems)),
        ]);
    }

    /**
     * @return array<int, array{id: int, title: string}>
     */
    public function customPages(): array
    {
        return CustomPage::query()
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (CustomPage $page): array => ['id' => $page->id, 'title' => $page->title])
            ->all();
    }

    private function upsertSettings(array $settings): void
    {
        SettingsStore::setAbout($settings);
    }
}; ?>

<section class="w-full">
    <div class="w-full">
        <flux:heading level="1" size="xl">{{ __('About Idol & Fansite') }}</flux:heading>
        <flux:subheading>{{ __('Kelola informasi Idol dan Fansite.') }}</flux:subheading>

        <div class="mt-5 space-y-6">
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

                        <flux:input wire:model="idolShortname" :label="__('Nama Panggilan (Idol Shortname)')" type="text" placeholder="Contoh: Oniel" />
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Digunakan untuk mencocokkan lineup jadwal teater dari JKT48Connect.') }}</flux:text>

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
                        <flux:heading size="lg">Kabesha</flux:heading>

                        <label class="inline-flex items-center gap-2 text-sm font-medium">
                            <input type="checkbox" wire:model.live="kabeshaEnabled" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                            Tampilkan Kabesha
                        </label>

                        <flux:input wire:model="kabeshaDefaultTitle" :label="__('Default Judul Kabesha')" type="text" placeholder="Contoh: Kabesha" />
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Dipakai untuk foto Kabesha yang tidak diberi judul.') }}</flux:text>

                        @if ($kabeshaEnabled)
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Tambah Foto Kabesha (bisa lebih dari satu)</label>
                            <input type="file" wire:model="kabeshaPhotoUploads" accept="image/*" multiple class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                            @error('kabeshaPhotoUploads')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('kabeshaPhotoUploads.*')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Foto baru masuk ke daftar setelah disimpan. Setiap foto punya Judul dan Duration sendiri.') }}</flux:text>
                        </div>

                        @if (count($this->kabeshaItemsWithPreview()) > 0)
                            <div
                                class="space-y-4"
                                x-data="{
                                    dragging: null,
                                    over: null,
                                    start(index) { this.dragging = index; },
                                    end() { this.dragging = null; this.over = null; },
                                    drop(index) {
                                        if (this.dragging === null || this.dragging === index) { this.end(); return; }
                                        const total = {{ count($this->kabeshaItemsWithPreview()) }};
                                        const order = Array.from({ length: total }, (_, i) => i);
                                        const [moved] = order.splice(this.dragging, 1);
                                        order.splice(index, 0, moved);
                                        this.end();
                                        $wire.reorderKabeshaItems(order);
                                    },
                                }"
                            >
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Tarik ikon gagang untuk mengubah urutan foto.') }}</flux:text>

                                @foreach ($this->kabeshaItemsWithPreview() as $index => $item)
                                    <div
                                        wire:key="kabesha-item-{{ $index }}"
                                        x-on:dragover.prevent="over = {{ $index }}"
                                        x-on:drop.prevent="drop({{ $index }})"
                                        x-bind:class="dragging !== null && over === {{ $index }} && dragging !== {{ $index }} ? 'border-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-900/60' : ''"
                                        class="rounded-lg border border-zinc-200 p-3 transition dark:border-zinc-700"
                                    >
                                        <div class="flex flex-col gap-4 sm:flex-row">
                                            <div class="flex items-start gap-3">
                                                <span
                                                    draggable="true"
                                                    x-on:dragstart="start({{ $index }})"
                                                    x-on:dragend="end()"
                                                    class="mt-1 flex size-8 shrink-0 cursor-grab items-center justify-center rounded-md border border-zinc-200 text-zinc-400 transition hover:text-zinc-700 active:cursor-grabbing dark:border-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-200"
                                                    title="{{ __('Drag untuk ubah urutan') }}"
                                                    aria-label="{{ __('Drag untuk ubah urutan') }}"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75Zm0 5A.75.75 0 0 1 2.75 9h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 9.75Zm0 5a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                                                </span>

                                                @if ($item['preview'])
                                                    <img src="{{ $item['preview'] }}" alt="Kabesha {{ $index + 1 }}" class="h-28 w-28 shrink-0 rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                                                @endif
                                            </div>

                                            <div class="flex-1 space-y-3">
                                                <flux:input wire:model="kabeshaItems.{{ $index }}.title" :label="__('Judul')" type="text" />

                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <flux:input wire:model="kabeshaItems.{{ $index }}.duration_from" :label="__('Duration From')" type="date" />
                                                    <flux:input wire:model="kabeshaItems.{{ $index }}.duration_to" :label="__('Duration To')" type="date" />
                                                </div>

                                                @error("kabeshaItems.{$index}.duration_to")
                                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div class="flex sm:flex-col">
                                                <flux:button
                                                    type="button"
                                                    size="sm"
                                                    variant="danger"
                                                    icon="trash"
                                                    wire:click="removeKabeshaItem({{ $index }})"
                                                    wire:confirm="Hapus foto Kabesha ini?"
                                                >
                                                    {{ __('Hapus') }}
                                                </flux:button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Belum ada foto Kabesha.') }}</p>
                        @endif
                        @endif
                    </div>

                    <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">Profile Details (Biodata)</flux:heading>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Versi Profil</label>
                            <div class="flex flex-wrap gap-6">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" wire:model.live="idolProfileVersion" value="jkt48" class="h-4 w-4 border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600">
                                    JKT48 Version
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" wire:model.live="idolProfileVersion" value="general" class="h-4 w-4 border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600">
                                    General Version
                                </label>
                            </div>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                @if ($idolProfileVersion === 'jkt48')
                                    {{ __('Menampilkan Jikoshoukai & Golongan Darah; sebutan "Oshimen".') }}
                                @else
                                    {{ __('Menyembunyikan Jikoshoukai & Golongan Darah; sebutan "Idol/Bias".') }}
                                @endif
                            </flux:text>
                        </div>

                        @if ($idolProfileVersion === 'jkt48')
                            <flux:textarea wire:model="idolJikoshoukai" :label="__('Jikoshoukai/Salam Perkenalan')" rows="3" />
                        @endif

                        <flux:input wire:model="idolBirthDate" :label="__('Tanggal Lahir')" type="date" />
                        <flux:input wire:model="idolBirthPlace" :label="__('Tempat Lahir')" type="text" />

                        <div class="grid gap-4 md:grid-cols-2">
                            @if ($idolProfileVersion === 'jkt48')
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
                            @endif

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

                        <label class="inline-flex items-center gap-2 text-sm font-medium">
                            <input type="checkbox" wire:model="fanbaseStructureEnabled" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                            Tampilkan Struktur Organisasi
                        </label>

                        <flux:textarea wire:model="fanbaseStructure" :label="__('Struktur Organisasi')" rows="4" />
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Satu baris = satu entri. Ditampilkan sebagai list di halaman fansite.') }}</flux:text>
                    </div>

                    <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">Activities and Gallery</flux:heading>

                        <label class="inline-flex items-center gap-2 text-sm font-medium">
                            <input type="checkbox" wire:model="fanbaseActivitiesEnabled" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                            Tampilkan Kegiatan Fanbase
                        </label>

                        <flux:textarea wire:model="fanbaseActivities" :label="__('Activities')" rows="4" />

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Gallery (maksimal 20 gambar)</label>
                            <input type="file" wire:model="fanbaseGalleryUploads" accept="image/*" multiple class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                            @error('fanbaseGalleryUploads')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('fanbaseGalleryUploads.*')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Foto baru masuk ke daftar setelah disimpan. Setiap foto bisa diberi caption.') }}</flux:text>
                        </div>

                        @if (count($this->fanbaseGalleryItemsWithPreview()) > 0)
                            <div class="space-y-4">
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ count($this->fanbaseGalleryItemsWithPreview()) }} / 20 {{ __('foto') }}</flux:text>

                                @foreach ($this->fanbaseGalleryItemsWithPreview() as $index => $item)
                                    <div wire:key="fanbase-gallery-item-{{ $index }}" class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                        <div class="flex flex-col gap-4 sm:flex-row">
                                            @if ($item['preview'])
                                                <img src="{{ $item['preview'] }}" alt="Galeri {{ $index + 1 }}" class="h-28 w-28 shrink-0 rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                                            @endif

                                            <div class="flex-1 space-y-3">
                                                <flux:input wire:model="fanbaseGalleryItems.{{ $index }}.caption" :label="__('Caption')" type="text" />
                                            </div>

                                            <div class="flex sm:flex-col">
                                                <flux:button
                                                    type="button"
                                                    size="sm"
                                                    variant="danger"
                                                    icon="trash"
                                                    wire:click="removeFanbaseGalleryItem({{ $index }})"
                                                    wire:confirm="Hapus foto galeri ini?"
                                                >
                                                    {{ __('Hapus') }}
                                                </flux:button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Belum ada foto galeri.') }}</p>
                        @endif
                    </div>

                    <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">Sejarah Fansite</flux:heading>

                        <label class="inline-flex items-center gap-2 text-sm font-medium">
                            <input type="checkbox" wire:model.live="fanbaseHistoryEnabled" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                            Tampilkan Sejarah Fansite
                        </label>

                        @if ($fanbaseHistoryEnabled)
                            <div class="space-y-3">
                                <p class="text-sm font-medium">Sumber Sejarah</p>
                                <div class="flex flex-wrap gap-6">
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="radio" wire:model.live="fanbaseHistorySource" value="default" class="h-4 w-4 border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600">
                                        Default Pages
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="radio" wire:model.live="fanbaseHistorySource" value="custom" class="h-4 w-4 border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600">
                                        Custom Pages
                                    </label>
                                </div>
                            </div>

                            @if ($fanbaseHistorySource === 'custom')
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Pilih Page</label>
                                    <select wire:model="fanbaseHistoryCustomPageId" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                        <option value="">-- Pilih Page --</option>
                                        @foreach ($this->customPages() as $customPageOption)
                                            <option value="{{ $customPageOption['id'] }}">{{ $customPageOption['title'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('fanbaseHistoryCustomPageId')
                                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Gambar & Deskripsi (maksimal 20)</label>
                                    <input type="file" wire:model="fanbaseHistoryUploads" accept="image/*" multiple class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                    @error('fanbaseHistoryUploads')
                                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                    @error('fanbaseHistoryUploads.*')
                                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Foto baru masuk ke daftar setelah disimpan. Setiap gambar punya deskripsi sendiri.') }}</flux:text>
                                </div>

                                @if (count($this->fanbaseHistoryItemsWithPreview()) > 0)
                                    <div class="space-y-4">
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ count($this->fanbaseHistoryItemsWithPreview()) }} / 20 {{ __('gambar') }}</flux:text>

                                        @foreach ($this->fanbaseHistoryItemsWithPreview() as $index => $item)
                                            <div wire:key="fanbase-history-item-{{ $index }}" class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                                <div class="flex flex-col gap-4 sm:flex-row">
                                                    @if ($item['preview'])
                                                        <img src="{{ $item['preview'] }}" alt="Sejarah {{ $index + 1 }}" class="h-28 w-28 shrink-0 rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                                                    @endif

                                                    <div class="flex-1 space-y-3">
                                                        <flux:textarea wire:model="fanbaseHistoryItems.{{ $index }}.description" :label="__('Deskripsi')" rows="3" />
                                                    </div>

                                                    <div class="flex sm:flex-col">
                                                        <flux:button
                                                            type="button"
                                                            size="sm"
                                                            variant="danger"
                                                            icon="trash"
                                                            wire:click="removeFanbaseHistoryItem({{ $index }})"
                                                            wire:confirm="Hapus gambar sejarah ini?"
                                                        >
                                                            {{ __('Hapus') }}
                                                        </flux:button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Belum ada gambar sejarah.') }}</p>
                                @endif
                            @endif
                        @endif
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
    </div>
</section>
