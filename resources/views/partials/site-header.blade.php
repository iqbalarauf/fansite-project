@php
    use App\Support\SettingBag;

    $__app = SettingBag::app();
    $__about = SettingBag::about();

    $__appName = $__app['app_name'] ?? config('app.name', 'Laravel');
    $__sidebarName = $__app['sidebar_name'] ?? config('app.name', 'Laravel');
    $__appLogo = $__app['app_logo'] ?? null;

    $__instagramUrl = $__about['instagram_url'] ?? $__about['idol_social_media_instagram'] ?? null;
    $__twitterUrl = $__about['twitter_url'] ?? $__about['idol_social_media_twitter'] ?? null;
    $__tiktokUrl = $__about['tiktok_url'] ?? $__about['idol_social_media_tiktok'] ?? null;

    $__idolName = $__about['idol_name'] ?? 'Oshimen';
    $__fanbaseName = $__about['fanbase_name'] ?? 'Fansite';

    $__active = $active ?? 'home';
    $__isAboutPage = in_array($__active, ['idol', 'fansite'], true);
    $__navItem = fn (string $item): string => $item
        ? 'text-indigo-600 dark:text-indigo-400'
        : 'text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400';
@endphp
<header class="sticky top-0 z-50 border-b border-slate-200/60 bg-white/50 shadow-sm backdrop-blur dark:border-slate-800/60 dark:bg-slate-900/50">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl text-lg font-bold text-white shadow-sm">
                @if ($__appLogo)
                    <img src="{{ Storage::url($__appLogo) }}" alt="{{ $__sidebarName }}" class="h-full w-full object-cover" />
                @else
                    <span class="flex h-full w-full items-center justify-center rounded-xl bg-indigo-600 text-slate-900">{{ strtoupper(substr($__sidebarName, 0, 1)) ?: 'F' }}</span>
                @endif
            </div>
            <span class="text-xl font-black text-slate-900 dark:text-white">{{ $__sidebarName }}</span>
        </a>

        <nav class="hidden items-center gap-1 text-sm font-medium md:flex">
            <a href="{{ route('home') }}"
               class="rounded-full px-4 py-2 transition {{ $__navItem($__active === 'home') }}">Home</a>

            <details class="relative">
                <summary class="flex cursor-pointer list-none items-center gap-1 rounded-full px-4 py-2 transition [&::-webkit-details-marker]:hidden {{ $__navItem($__isAboutPage) }}">
                    <span>About</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                    </svg>
                </summary>
                <div class="absolute right-0 top-full mt-2 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                    <a href="{{ route('about.idol') }}"
                       class="flex items-center gap-2 rounded-xl px-3 py-2.5 transition {{ $__active === 'idol' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400' }}">
                        {{ $__idolName }}
                    </a>
                    <a href="{{ route('about.fansite') }}"
                       class="flex items-center gap-2 rounded-xl px-3 py-2.5 transition {{ $__active === 'fansite' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400' }}">
                        {{ $__fanbaseName }}
                    </a>
                </div>
            </details>

            <a href="{{ route('home') }}#data"
               class="rounded-full px-4 py-2 transition {{ $__navItem(false) }}">Data</a>
            <a href="{{ route('home') }}#schedule"
               class="rounded-full px-4 py-2 transition {{ $__navItem(false) }}">Schedule</a>
        </nav>

        <div class="flex items-center gap-2">
            <x-social-media-icons :instagram="$__instagramUrl" :twitter="$__twitterUrl" :tiktok="$__tiktokUrl" />

            <button type="button" onclick="window.toggleTheme()" aria-label="Ganti mode terang/gelap"
                    class="flex size-9 items-center justify-center rounded-full border border-slate-200 bg-white/70 text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:text-indigo-400 dark:[&_.icon-sun]:hidden [&_.icon-moon]:hidden dark:[&_.icon-moon]:block">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-sun size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <circle cx="12" cy="12" r="4"/>
                    <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-moon size-4.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 0 1 6.707 2.707a8 8 0 1 0 10.586 10.586Z"/>
                </svg>
            </button>
        </div>
    </div>
</header>