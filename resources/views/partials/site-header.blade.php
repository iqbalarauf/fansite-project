@php
    use App\Support\HeaderMenu;
    use App\Support\SettingBag;

    $__app = SettingBag::app();
    $__about = SettingBag::about();

    $__appName = $__app['app_name'] ?? config('app.name', 'Laravel');
    $__sidebarName = $__app['sidebar_name'] ?? config('app.name', 'Laravel');
    $__appLogo = $__app['app_logo'] ?? null;
    $__hideAppName = filter_var($__app['header_hide_app_name'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

    $__instagramUrl = $__about['instagram_url'] ?? $__about['idol_social_media_instagram'] ?? null;
    $__twitterUrl = $__about['twitter_url'] ?? $__about['idol_social_media_twitter'] ?? null;
    $__tiktokUrl = $__about['tiktok_url'] ?? $__about['idol_social_media_tiktok'] ?? null;

    $__idolName = $__about['idol_name'] ?? 'Oshimen';
    $__fanbaseName = $__about['fanbase_name'] ?? 'Fansite';

    $__idolSlug = trim((string) ($__about['idol_slug'] ?? ''));
    if ($__idolSlug === '') {
        $__idolSlug = \Illuminate\Support\Str::slug($__idolName);
    }

    $__fanbaseSlug = trim((string) ($__about['fanbase_slug'] ?? ''));
    if ($__fanbaseSlug === '') {
        $__fanbaseSlug = \Illuminate\Support\Str::slug($__fanbaseName);
    }

    $__active = $active ?? 'home';
    $__isAboutPage = in_array($__active, ['idol', 'fansite'], true);
    $__isArticlePage = in_array($__active, ['news', 'blog'], true);
    $__newsEnabled = SettingBag::featureEnabled('news');
    $__blogEnabled = SettingBag::featureEnabled('blog');
    $__magazineEnabled = SettingBag::featureEnabled('magazines');
    $__triviaEnabled = SettingBag::featureEnabled('trivia');
    $__photoboothEnabled = SettingBag::featureEnabled('photobooth');
    $__merchandiseEnabled = SettingBag::featureEnabled('merchandise');
    $__photoboothSlug = $__photoboothEnabled ? \App\Models\Photobooth::current()?->slug : null;
    $__customMenu = HeaderMenu::isCustom();
    $__customMenuItems = $__customMenu ? HeaderMenu::tree() : [];
    $__navItem = fn (string $item): string => $item
        ? 'text-indigo-600 dark:text-indigo-400'
        : 'text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400';
@endphp
<header class="sticky top-0 z-50 border-b border-slate-200/60 bg-white/50 shadow-sm backdrop-blur dark:border-slate-800/60 dark:bg-slate-900/50" data-site-header>
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center overflow-hidden text-lg font-bold text-white">
                @if ($__appLogo)
                    <img src="{{ Storage::url($__appLogo) }}" alt="{{ $__sidebarName }}" class="h-full w-full object-cover" />
                @else
                    <span class="flex h-full w-full items-center justify-center rounded-xl bg-indigo-600 text-slate-900">{{ strtoupper(substr($__sidebarName, 0, 1)) ?: 'F' }}</span>
                @endif
            </div>
            @unless ($__hideAppName)
                <span class="text-xl font-black text-slate-900 dark:text-white">{{ $__sidebarName }}</span>
            @endunless
        </a>

        <nav class="hidden items-center gap-1 text-sm font-medium md:flex">
            @if ($__customMenu)
                @include('partials.header-menu', ['items' => $__customMenuItems, 'level' => 0])
            @else
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
                    <a href="{{ route('about.show', $__idolSlug) }}"
                       class="flex items-center gap-2 rounded-xl px-3 py-2.5 transition {{ $__active === 'idol' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400' }}">
                        {{ $__idolName }}
                    </a>
                    <a href="{{ route('about.show', $__fanbaseSlug) }}"
                       class="flex items-center gap-2 rounded-xl px-3 py-2.5 transition {{ $__active === 'fansite' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400' }}">
                        {{ $__fanbaseName }}
                    </a>
                </div>
            </details>

            @if ($__newsEnabled || $__blogEnabled)
                <details class="relative">
                    <summary class="flex cursor-pointer list-none items-center gap-1 rounded-full px-4 py-2 transition [&::-webkit-details-marker]:hidden {{ $__navItem($__isArticlePage) }}">
                        <span>Artikel</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                        </svg>
                    </summary>
                    <div class="absolute right-0 top-full mt-2 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                        @if ($__newsEnabled)
                            <a href="{{ route('news.index') }}"
                               class="flex items-center gap-2 rounded-xl px-3 py-2.5 transition {{ $__active === 'news' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400' }}">
                                News
                            </a>
                        @endif
                        @if ($__blogEnabled)
                            <a href="{{ route('blog.index') }}"
                               class="flex items-center gap-2 rounded-xl px-3 py-2.5 transition {{ $__active === 'blog' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400' }}">
                                Blog
                            </a>
                        @endif
                    </div>
                </details>
            @endif

            @if ($__magazineEnabled)
                <a href="{{ route('magazine.index') }}"
                   class="rounded-full px-4 py-2 transition {{ $__navItem($__active === 'magazine') }}">Majalah</a>
            @endif

            <a href="{{ route('gallery.index') }}"
               class="rounded-full px-4 py-2 transition {{ $__navItem($__active === 'gallery') }}">Galeri</a>

            @if ($__triviaEnabled)
                <a href="{{ route('trivia.index') }}"
                   class="rounded-full px-4 py-2 transition {{ $__navItem($__active === 'trivia') }}">Trivia</a>
            @endif

            @if ($__photoboothEnabled && $__photoboothSlug)
                <a href="{{ $__photoboothSlug === 'photobooth' ? route('photobooth.show') : route('photobooth.show', $__photoboothSlug) }}"
                   class="rounded-full px-4 py-2 transition {{ $__navItem($__active === 'photobooth') }}">Photobooth</a>
            @endif

            @if ($__merchandiseEnabled)
                <a href="{{ route('merchandise.index') }}"
                   class="rounded-full px-4 py-2 transition {{ $__navItem($__active === 'merchandise') }}">Merchandise</a>
            @endif

            <a href="{{ route('home') }}#data"
               class="rounded-full px-4 py-2 transition {{ $__navItem(false) }}">Data</a>
            <a href="{{ route('schedule.index') }}"
               class="rounded-full px-4 py-2 transition {{ $__navItem($__active === 'schedule') }}">Schedule</a>
            @endif
        </nav>

        <div class="flex items-center gap-2">
            {{-- Ikon sosial + switch tema: sebaris, di kiri pada mobile --}}
            <div class="items-center gap-2 hidden md:flex">
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

            {{-- Hamburger (mobile) --}}
            <button
                type="button"
                data-mobile-menu-toggle
                aria-label="{{ __('Buka menu') }}"
                aria-expanded="false"
                aria-controls="site-mobile-menu"
                class="ml-auto flex size-9 items-center justify-center text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:text-indigo-400 md:hidden"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Panel menu mobile --}}
    <div id="site-mobile-menu" data-mobile-menu class="hidden border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 md:hidden">
        <nav class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-4 text-sm font-medium sm:px-6">
            @if ($__customMenu)
                @include('partials.header-menu', ['items' => $__customMenuItems, 'level' => 0])
            @else
                <a href="{{ route('home') }}" class="rounded-xl px-4 py-3 transition {{ $__navItem($__active === 'home') }}">Home</a>

                <details class="rounded-xl">
                    <summary class="flex cursor-pointer list-none items-center justify-between rounded-xl px-4 py-3 transition [&::-webkit-details-marker]:hidden {{ $__navItem($__isAboutPage) }}">
                        <span>About</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                        </svg>
                    </summary>
                    <div class="flex flex-col gap-1 pb-1 pl-3">
                        <a href="{{ route('about.show', $__idolSlug) }}" class="rounded-xl px-4 py-2.5 transition {{ $__active === 'idol' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400' }}">{{ $__idolName }}</a>
                        <a href="{{ route('about.show', $__fanbaseSlug) }}" class="rounded-xl px-4 py-2.5 transition {{ $__active === 'fansite' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400' }}">{{ $__fanbaseName }}</a>
                    </div>
                </details>

                @if ($__newsEnabled || $__blogEnabled)
                    <details class="rounded-xl">
                        <summary class="flex cursor-pointer list-none items-center justify-between rounded-xl px-4 py-3 transition [&::-webkit-details-marker]:hidden {{ $__navItem($__isArticlePage) }}">
                            <span>Artikel</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                            </svg>
                        </summary>
                        <div class="flex flex-col gap-1 pb-1 pl-3">
                            @if ($__newsEnabled)
                                <a href="{{ route('news.index') }}" class="rounded-xl px-4 py-2.5 transition {{ $__active === 'news' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400' }}">News</a>
                            @endif
                            @if ($__blogEnabled)
                                <a href="{{ route('blog.index') }}" class="rounded-xl px-4 py-2.5 transition {{ $__active === 'blog' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400' }}">Blog</a>
                            @endif
                        </div>
                    </details>
                @endif

                @if ($__magazineEnabled)
                    <a href="{{ route('magazine.index') }}" class="rounded-xl px-4 py-3 transition {{ $__navItem($__active === 'magazine') }}">Majalah</a>
                @endif

                <a href="{{ route('gallery.index') }}" class="rounded-xl px-4 py-3 transition {{ $__navItem($__active === 'gallery') }}">Galeri</a>

                @if ($__triviaEnabled)
                    <a href="{{ route('trivia.index') }}" class="rounded-xl px-4 py-3 transition {{ $__navItem($__active === 'trivia') }}">Trivia</a>
                @endif

                @if ($__photoboothEnabled && $__photoboothSlug)
                    <a href="{{ $__photoboothSlug === 'photobooth' ? route('photobooth.show') : route('photobooth.show', $__photoboothSlug) }}" class="rounded-xl px-4 py-3 transition {{ $__navItem($__active === 'photobooth') }}">Photobooth</a>
                @endif

                @if ($__merchandiseEnabled)
                    <a href="{{ route('merchandise.index') }}" class="rounded-xl px-4 py-3 transition {{ $__navItem($__active === 'merchandise') }}">Merchandise</a>
                @endif

                <a href="{{ route('home') }}#data" class="rounded-xl px-4 py-3 transition {{ $__navItem(false) }}">Data</a>
                <a href="{{ route('schedule.index') }}" class="rounded-xl px-4 py-3 transition {{ $__navItem($__active === 'schedule') }}">Schedule</a>
            @endif

            <div class="mt-3 flex items-center justify-start gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                <x-social-media-icons :instagram="$__instagramUrl" :twitter="$__twitterUrl" :tiktok="$__tiktokUrl" />

                <button type="button" onclick="window.toggleTheme()" aria-label="Ganti mode terang/gelap"
                        class="flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:text-indigo-400">
                    <span class="dark:hidden">{{ __('Mode Gelap') }}</span>
                    <span class="hidden dark:inline">{{ __('Mode Terang') }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="hidden size-4 dark:inline" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <circle cx="12" cy="12" r="4"/>
                        <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                    </svg>
                </button>
            </div>
        </nav>
    </div>
</header>

<script>
    (function () {
        var header = document.querySelector('[data-site-header]');

        if (!header) {
            return;
        }

        function allDetails() {
            return header.querySelectorAll('details');
        }

        // The `toggle` event does not bubble, so listen during the capture phase.
        header.addEventListener('toggle', function (event) {
            var target = event.target;

            if (target.tagName !== 'DETAILS' || !target.open) {
                return;
            }

            allDetails().forEach(function (details) {
                if (details !== target && details.open && !details.contains(target)) {
                    details.open = false;
                }
            });
        }, true);

        // Close any open dropdown when clicking outside the header.
        document.addEventListener('click', function (event) {
            if (header.contains(event.target)) {
                return;
            }

            allDetails().forEach(function (details) {
                details.open = false;
            });
        });

        // Mobile hamburger menu.
        var toggle = header.querySelector('[data-mobile-menu-toggle]');
        var mobileMenu = header.querySelector('[data-mobile-menu]');

        if (toggle && mobileMenu) {
            toggle.addEventListener('click', function () {
                var isOpen = !mobileMenu.classList.contains('hidden');

                mobileMenu.classList.toggle('hidden', isOpen);
                toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            });
        }
    })();
</script>
