@props([
    'class' => 'size-9',
])

@php
    $appSettings = DB::table('app_settings')->pluck('value', 'key')->all();
    $brandName = $appSettings['sidebar_name'] ?? config('app.name', 'Laravel');
    $brandLogo = $appSettings['app_logo'] ?? null;
@endphp

@if ($brandLogo)
    <img src="{{ Storage::url($brandLogo) }}" alt="{{ $brandName }}" {{ $attributes->merge(['class' => $class.' rounded-md object-cover']) }} />
@else
    <x-app-logo-icon {{ $attributes->merge(['class' => $class.' fill-current text-black dark:text-white']) }} />
@endif
