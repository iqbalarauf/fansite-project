<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@php
    $appSettings = DB::table('app_settings')->pluck('value', 'key')->all();
    $appName = $appSettings['app_name'] ?? config('app.name', 'Laravel');
    $appLogo = $appSettings['app_logo'] ?? null;
@endphp

<title>
    {{ filled($title ?? null) ? $title.' - '.$appName : $appName }}
</title>

@if ($appLogo)
    <link rel="icon" href="{{ Storage::url($appLogo) }}">
    <link rel="apple-touch-icon" href="{{ Storage::url($appLogo) }}">
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
@endif

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

@if (filled($appSettings['brand_color'] ?? null))
    <style>
        :root {
            --brand-primary: {{ $appSettings['brand_color'] }};
            --brand-primary-strong: {{ $appSettings['brand_color'] }};
            --color-primary: {{ $appSettings['brand_color'] }};
        }
    </style>
@endif
