<?php

use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Features Activation')] class extends Component {
    public bool $newsEnabled = true;
    public bool $blogEnabled = true;
    public bool $magazineEnabled = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $settings = DB::table('app_settings')->pluck('value', 'key')->all();

        $this->newsEnabled = filter_var($settings['news_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->blogEnabled = filter_var($settings['blog_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->magazineEnabled = filter_var($settings['magazines_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
    }

    public function save(): void
    {
        foreach ([
            'news_enabled' => $this->newsEnabled,
            'blog_enabled' => $this->blogEnabled,
            'magazines_enabled' => $this->magazineEnabled,
        ] as $key => $enabled) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $enabled ? 'true' : 'false', 'updated_at' => now()],
            );
        }

        Cache::forget('app_settings');

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
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
