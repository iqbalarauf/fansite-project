<?php

use App\Support\BrandPalette;
use App\Support\ImageOptimizer;
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

    public ?string $loginImagePath = null;

    public mixed $loginImageUpload = null;

    public function mount(): void
    {
        $settings = DB::table('app_settings')->pluck('value', 'key')->all();

        $this->brandColor = BrandPalette::normalize($settings['brand_color'] ?? null, BrandPalette::PRIMARY);
        $this->brandColorSecondary = BrandPalette::normalize($settings['brand_color_secondary'] ?? null, BrandPalette::SECONDARY);
        $this->brandColorTertiary = BrandPalette::normalize($settings['brand_color_tertiary'] ?? null, BrandPalette::TERTIARY);
        $this->appName = (string) ($settings['app_name'] ?? '');
        $this->descApp = (string) ($settings['desc_app'] ?? '');
        $this->appLogoPath = $settings['app_logo'] ?? null;
        $this->loginImagePath = $settings['login_image'] ?? null;
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
            'loginImageUpload' => ['nullable', 'image', 'max:3072'],
        ]);

        if ($this->appLogoUpload) {
            if ($this->appLogoPath) {
                Storage::disk('public')->delete($this->appLogoPath);
            }

            $this->appLogoPath = ImageOptimizer::store($this->appLogoUpload, 'app', maxWidth: 512);
            $this->appLogoUpload = null;
        }

        if ($this->loginImageUpload) {
            if ($this->loginImagePath) {
                Storage::disk('public')->delete($this->loginImagePath);
            }

            $this->loginImagePath = ImageOptimizer::store($this->loginImageUpload, 'app/login');
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
            'login_image' => $this->loginImagePath,
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
}; ?>

<section class="w-full">
    <div>
        <flux:heading level="1" size="xl">{{ __('Appearance') }}</flux:heading>
        <flux:subheading>{{ __('Kelola warna brand dan identitas situs') }}</flux:subheading>
    </div>

    <div class="mt-5 w-full">
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

                    <div class="grid gap-4 md:grid-cols-3">
                        @foreach ($brandFields as $field)
                            <div class="flex flex-col gap-3 rounded-lg border border-dashed border-zinc-300 p-3 dark:border-zinc-600">
                                <div class="flex items-center gap-3">
                                    <input
                                        type="color"
                                        wire:model.live="{{ $field['key'] }}"
                                        class="h-11 w-14 shrink-0 cursor-pointer rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-800"
                                        aria-label="{{ __('Color picker') }} {{ $field['label'] }}"
                                    />
                                    <span class="inline-flex size-9 shrink-0 rounded-full border border-zinc-200 shadow-sm dark:border-zinc-700" style="background-color: {{ $this->{$field['key']} ?: '#6C7CE8' }}"></span>
                                </div>
                                <flux:input wire:model.live="{{ $field['key'] }}" :label="__($field['label'])" type="text" placeholder="#6C7CE8" maxlength="7" />
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $field['usage'] }}</flux:text>
                                @error($field['key'])
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('App Identity') }}</flux:heading>

                    <flux:input wire:model="appName" :label="__('App Name / Sidebar Name')" type="text" required />
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Nilai ini dipakai sebagai App Name sekaligus Sidebar Name.') }}</flux:text>

                    <flux:textarea wire:model="descApp" :label="__('Desc App')" rows="4" />

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <flux:label>{{ __('App Logo') }}</flux:label>
                            @if ($this->appLogoPreviewUrl())
                                <img src="{{ $this->appLogoPreviewUrl() }}" alt="App Logo" class="h-auto w-full rounded-lg border border-zinc-200 object-contain dark:border-zinc-700">
                            @else
                                <div class="flex aspect-video w-full items-center justify-center rounded-lg border border-dashed border-zinc-300 text-xs text-zinc-400 dark:border-zinc-600">
                                    <flux:icon.photo variant="micro" />
                                </div>
                            @endif
                            <input type="file" wire:model="appLogoUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                            @error('appLogoUpload')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-2">
                            <flux:label>{{ __('Login Image') }}</flux:label>
                            @if ($this->loginImagePreviewUrl())
                                <img src="{{ $this->loginImagePreviewUrl() }}" alt="Login Image" class="h-auto w-full rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                            @else
                                <div class="flex aspect-video w-full items-center justify-center rounded-lg border border-dashed border-zinc-300 text-xs text-zinc-400 dark:border-zinc-600">
                                    <flux:icon.photo variant="micro" />
                                </div>
                            @endif
                            <input type="file" wire:model="loginImageUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Ditampilkan sebagai panel gambar pada halaman login.') }}</flux:text>
                            @error('loginImageUpload')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                </div>
            </form>
        @else
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pengaturan tampilan hanya dapat diubah oleh Super Admin.') }}</p>
        @endif
    </div>
</section>
