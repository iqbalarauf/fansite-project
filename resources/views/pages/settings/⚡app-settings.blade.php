<?php

use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('App settings')] class extends Component {
    use WithFileUploads;

    public string $appName = '';
    public string $sidebarName = '';
    public string $descApp = '';
    public ?string $appLogoPath = null;
    public mixed $appLogoUpload = null;
    public ?string $heroImagePath = null;
    public mixed $heroImageUpload = null;

    public function mount(): void
    {
        $settings = DB::table('app_settings')->pluck('value', 'key')->all();

        $this->appName = (string) ($settings['app_name'] ?? '');
        $this->sidebarName = (string) ($settings['sidebar_name'] ?? '');
        $this->descApp = (string) ($settings['desc_app'] ?? '');
        $this->appLogoPath = $settings['app_logo'] ?? null;
        $this->heroImagePath = $settings['hero_image'] ?? null;
    }

    public function save(): void
    {
        $this->validate([
            'appName' => ['required', 'string', 'max:255'],
            'sidebarName' => ['required', 'string', 'max:255'],
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
            'app_name' => $this->appName,
            'sidebar_name' => $this->sidebarName,
            'desc_app' => $this->descApp,
            'app_logo' => $this->appLogoPath,
            'hero_image' => $this->heroImagePath,
        ] as $key => $value) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()],
            );
        }

        Flux::toast(variant: 'success', text: __('App settings updated.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('App settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('App Settings')" :subheading="__('Manage the public identity and branding for your fansite')">
        <form wire:submit="save" class="space-y-6">
            <flux:input wire:model="appName" :label="__('App Name')" type="text" required />
            <flux:input wire:model="sidebarName" :label="__('Sidebar Name')" type="text" required />
            <flux:textarea wire:model="descApp" :label="__('Desc App')" rows="4" />

            <div class="space-y-2">
                <flux:label>{{ __('App Logo') }}</flux:label>
                <input type="file" wire:model="appLogoUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                @if ($appLogoPath)
                    <img src="{{ Storage::url($appLogoPath) }}" alt="App Logo" class="mt-2 h-20 w-auto rounded-lg border border-zinc-200 object-contain dark:border-zinc-700">
                @endif
            </div>

            <div class="space-y-2">
                <flux:label>{{ __('Hero Image') }}</flux:label>
                <input type="file" wire:model="heroImageUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                @if ($heroImagePath)
                    <img src="{{ Storage::url($heroImagePath) }}" alt="Hero Image" class="mt-2 h-28 w-full rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                @endif
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
