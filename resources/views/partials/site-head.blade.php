@php
    use App\Support\SettingBag;

    $__app = SettingBag::app();
    $__appName = $__app['app_name'] ?? config('app.name', 'Laravel');
    $__appLogo = $__app['app_logo'] ?? null;
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ filled($title ?? null) ? $title.' — '.$__appName : $__appName }}</title>
@if (filled($metaDescription ?? null))
    <meta name="description" content="{{ $metaDescription }}">
@endif
<meta property="og:site_name" content="{{ $__appName }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ filled($title ?? null) ? $title.' — '.$__appName : $__appName }}">
@if (filled($metaDescription ?? null))
    <meta property="og:description" content="{{ $metaDescription }}">
@endif
@if (filled($ogImage ?? null))
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
@endif
@if ($__appLogo)
    <link rel="icon" href="{{ Storage::url($__appLogo) }}">
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
@endif
@vite(['resources/css/app.css', 'resources/js/app.js'])

<script>
    (function () {
        var storageKey = 'fansite-theme';
        var root = document.documentElement;
        var stored = localStorage.getItem(storageKey);
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (stored ? stored === 'dark' : prefersDark) {
            root.classList.add('dark');
        }

        window.toggleTheme = function () {
            root.classList.toggle('dark');
            localStorage.setItem(storageKey, root.classList.contains('dark') ? 'dark' : 'light');
        };
    })();
</script>