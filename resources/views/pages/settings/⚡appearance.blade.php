<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Appearance settings')] class extends Component {
    public string $palette = 'periwinkle';

    public function updatedPalette(string $value): void
    {
        $this->dispatch('brand-palette-updated', palette: $value);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <div x-data="{
            palette: '{{ $palette }}',
            setPalette(value) {
                this.palette = value;
                document.body.dataset.brandPalette = value;
            },
            init() {
                document.body.dataset.brandPalette = this.palette;
            }
        }" class="space-y-6">
            <div class="space-y-3">
                <flux:heading size="sm">{{ __('Theme mode') }}</flux:heading>
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>
            </div>

            <div class="space-y-3">
                <flux:heading size="sm">{{ __('Visual palette') }}</flux:heading>

                <div class="grid gap-3 md:grid-cols-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="palette" value="periwinkle" x-model="palette" @change="setPalette($event.target.value)" class="sr-only" checked>
                        <div class="rounded-2xl border p-4 transition-all duration-200" :class="palette === 'periwinkle' ? 'border-[#6C7CE8] bg-[#F5F6FA] shadow-sm ring-2 ring-[#6C7CE8]/20' : 'border-zinc-200 bg-white hover:border-[#6C7CE8]/60'">
                            <div class="flex items-center gap-2">
                                <span class="size-5 rounded-full border border-white shadow-sm" style="background:#6C7CE8"></span>
                                <span class="size-5 rounded-full border border-white shadow-sm" style="background:#4E5FD4"></span>
                                <span class="size-5 rounded-full border border-white shadow-sm" style="background:#FFD166"></span>
                            </div>
                            <div class="mt-4 text-base font-semibold text-zinc-800">Periwinkle</div>
                            <div class="mt-1 text-xs text-zinc-500">Primary / navbar / CTA</div>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="palette" value="gold" x-model="palette" @change="setPalette($event.target.value)" class="sr-only">
                        <div class="rounded-2xl border p-4 transition-all duration-200" :class="palette === 'gold' ? 'border-[#F4C95D] bg-[#FFF9EE] shadow-sm ring-2 ring-[#F4C95D]/20' : 'border-zinc-200 bg-white hover:border-[#F4C95D]/60'">
                            <div class="flex items-center gap-2">
                                <span class="size-5 rounded-full border border-white shadow-sm" style="background:#F4C95D"></span>
                                <span class="size-5 rounded-full border border-white shadow-sm" style="background:#FFD166"></span>
                                <span class="size-5 rounded-full border border-white shadow-sm" style="background:#A5B4FC"></span>
                            </div>
                            <div class="mt-4 text-base font-semibold text-zinc-800">Warm Gold</div>
                            <div class="mt-1 text-xs text-zinc-500">Accent / sparkle / badge</div>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="palette" value="lavender" x-model="palette" @change="setPalette($event.target.value)" class="sr-only">
                        <div class="rounded-2xl border p-4 transition-all duration-200" :class="palette === 'lavender' ? 'border-[#A5B4FC] bg-[#F5F7FF] shadow-sm ring-2 ring-[#A5B4FC]/20' : 'border-zinc-200 bg-white hover:border-[#A5B4FC]/60'">
                            <div class="flex items-center gap-2">
                                <span class="size-5 rounded-full border border-white shadow-sm" style="background:#A5B4FC"></span>
                                <span class="size-5 rounded-full border border-white shadow-sm" style="background:#7D8EF3"></span>
                                <span class="size-5 rounded-full border border-white shadow-sm" style="background:#F5F6FA"></span>
                            </div>
                            <div class="mt-4 text-base font-semibold text-zinc-800">Soft Lavender</div>
                            <div class="mt-1 text-xs text-zinc-500">Background / highlight</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </x-pages::settings.layout>
</section>
