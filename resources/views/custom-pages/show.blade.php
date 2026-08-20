@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;

    $appSettings = DB::table('app_settings')->pluck('value', 'key')->all();
    $about = DB::table('about_settings')->pluck('value', 'key')->all();
    $appName = $appSettings['app_name'] ?? config('app.name', 'Laravel');
    $sidebarName = $appSettings['sidebar_name'] ?? $appName;
    $appLogo = $appSettings['app_logo'] ?? null;
    $instagramUrl = $about['instagram_url'] ?? $about['idol_social_media_instagram'] ?? null;
    $twitterUrl = $about['twitter_url'] ?? $about['idol_social_media_twitter'] ?? null;
    $tiktokUrl = $about['tiktok_url'] ?? $about['idol_social_media_tiktok'] ?? null;
    $background = match ($page->background_color ?? 'slate') {
        'white' => 'bg-white',
        'indigo' => 'bg-indigo-50',
        default => 'bg-slate-100',
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $page->title }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen {{ $background }} text-slate-800 antialiased">
        @if (($page->display_mode ?? 'full') === 'welcome')
            <header class="bg-white shadow-sm">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-indigo-600 text-lg font-bold text-white shadow-sm">
                            @if ($appLogo)
                                <img src="{{ Storage::url($appLogo) }}" alt="{{ $sidebarName }}" class="h-full w-full object-cover">
                            @else
                                {{ strtoupper(substr($sidebarName, 0, 1)) ?: 'F' }}
                            @endif
                        </span>
                        <span class="text-xl font-black text-slate-900">{{ $sidebarName }}</span>
                    </a>
                    <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
                        <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
                        <a href="{{ route('home') }}#about" class="hover:text-indigo-600">About</a>
                        <a href="{{ route('home') }}#data" class="hover:text-indigo-600">Data</a>
                        <a href="{{ route('home') }}#schedule" class="hover:text-indigo-600">Schedule</a>
                    </nav>
                    <div class="flex items-center gap-2">
                        @foreach ([[$instagramUrl, 'Instagram'], [$twitterUrl, 'X'], [$tiktokUrl, 'TikTok']] as [$url, $label])
                            @if ($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:border-indigo-300 hover:text-indigo-600">{{ $label }}</a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </header>
        @endif

        <main class="mx-auto flex min-h-screen w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6 lg:py-16">
            <header class="border-b border-slate-200 pb-6">
                <h1 class="text-4xl font-black tracking-tight sm:text-5xl">{{ $page->title }}</h1>
            </header>
            <div class="space-y-5">
                @foreach ($page->blocks ?? [] as $block)
                    @include('custom-pages.render-block', ['block' => $block])
                @endforeach
            </div>
        </main>

        @if (($page->display_mode ?? 'full') === 'welcome')
            <footer class="border-t border-slate-200 bg-white">
                <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                    <div class="font-semibold text-slate-900">{{ $appName }}</div>
                    <p>© {{ now()->format('Y') }} {{ $appName }}</p>
                </div>
            </footer>
        @endif
    </body>
</html>
