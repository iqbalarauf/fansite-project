<?php

use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Appearance settings')] class extends Component {
    use WithFileUploads;

    public string $brandColor = '#6C7CE8';
    public string $appName = '';
    public string $descApp = '';
    public ?string $appLogoPath = null;
    public mixed $appLogoUpload = null;
    public ?string $heroImagePath = null;
    public mixed $heroImageUpload = null;

    public function mount(): void
    {
        $settings = DB::table('app_settings')->pluck('value', 'key')->all();

        $this->brandColor = (string) ($settings['brand_color'] ?? '#6C7CE8');
        $this->appName = (string) ($settings['app_name'] ?? '');
        $this->descApp = (string) ($settings['desc_app'] ?? '');
        $this->appLogoPath = $settings['app_logo'] ?? null;
        $this->heroImagePath = $settings['hero_image'] ?? null;
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $this->validate([
            'brandColor' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'appName' => ['required', 'string', 'max:255'],
            'descApp' => ['nullable', 'string'],
            'appLogoUpload' => ['nullable', 'image', 'max:3072'],
            'heroImageUpload' => ['nullable', 'image', 'max:3072'],
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

        foreach ([
            'brand_color' => strtoupper($this->brandColor),
            'app_name' => $this->appName,
            'sidebar_name' => $this->appName,
            'desc_app' => $this->descApp,
            'app_logo' => $this->appLogoPath,
            'hero_image' => $this->heroImagePath,
        ] as $key => $value) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()],
            );
        }

        $this->brandColor = strtoupper($this->brandColor);

        Cache::forget('app_settings');

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
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Kelola warna brand dan identitas situs')">
        @if (auth()->user()?->isSuperAdmin())
            <form wire:submit="save" class="space-y-6">
                <div class="space-y-3 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Brand Color') }}</flux:heading>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Warna utama situs dalam format HEX. Gunakan color picker atau isi kode HEX langsung.') }}</flux:text>

                    <div class="flex flex-wrap items-center gap-3">
                        <input
                            type="color"
                            wire:model.live="brandColor"
                            class="h-11 w-14 cursor-pointer rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-800"
                            aria-label="{{ __('Color picker') }}"
                        />
                        <flux:input wire:model.live="brandColor" type="text" placeholder="#6C7CE8" maxlength="7" class="w-40" />
                        <span class="inline-flex size-9 rounded-full border border-zinc-200 shadow-sm dark:border-zinc-700" style="background-color: {{ $brandColor ?: '#6C7CE8' }}"></span>
                    </div>

                    @error('brandColor')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
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
                            <img src="{{ $this->heroImagePreviewUrl() }}" alt="Hero Image" class="mt-2 h-28 w-full rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                        @endif
                    </div>
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
