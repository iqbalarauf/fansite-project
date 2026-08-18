@props([
    'sidebar' => false,
])

@php
    $appSettings = DB::table('app_settings')->pluck('value', 'key')->all();
    $brandName = $appSettings['sidebar_name'] ?? config('app.name', 'Laravel');
    $brandLogo = $appSettings['app_logo'] ?? null;
@endphp

@if($sidebar)
    <flux:sidebar.brand name="{{ $brandName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md text-accent-foreground">
            @if ($brandLogo)
                <img src="{{ Storage::url($brandLogo) }}" alt="{{ $brandName }}" class="h-full w-full object-cover" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="{{ $brandName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md text-accent-foreground">
            @if ($brandLogo)
                <img src="{{ Storage::url($brandLogo) }}" alt="{{ $brandName }}" class="h-full w-full object-cover" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:brand>
@endif
