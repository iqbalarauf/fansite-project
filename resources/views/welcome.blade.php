@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Carbon;

    $heroUrl = $heroImage ? Storage::url($heroImage) : null;
    $heroStyle = $heroUrl ? "background-image: url('".$heroUrl."');" : '';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $appName }}</title>
        @if ($appLogo)
            <link rel="icon" href="{{ Storage::url($appLogo) }}">
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
    </head>
    <body class="bg-slate-100 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
        <header class="sticky top-0 z-50 border-b border-slate-200/60 bg-white/50 shadow-sm backdrop-blur dark:border-slate-800/60 dark:bg-slate-900/50">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <a href="#home" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl text-lg font-bold text-white shadow-sm">
                        @if ($appLogo)
                            <img src="{{ Storage::url($appLogo) }}" alt="{{ $sidebarName }}" class="h-full w-full object-cover" />
                        @else
                            <span class="flex h-full w-full items-center justify-center rounded-xl bg-indigo-600 text-slate-900">{{ strtoupper(substr($sidebarName, 0, 1)) ?: 'F' }}</span>
                        @endif
                    </div>
                    <span class="text-xl font-black text-slate-900 dark:text-white">{{ $sidebarName }}</span>
                </a>

                <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 dark:text-slate-300 md:flex">
                    <a href="#home" class="hover:text-indigo-600 dark:hover:text-indigo-400">Home</a>
                    <a href="#about" class="hover:text-indigo-600 dark:hover:text-indigo-400">About</a>
                    <a href="#data" class="hover:text-indigo-600 dark:hover:text-indigo-400">Data</a>
                    <a href="#schedule" class="hover:text-indigo-600 dark:hover:text-indigo-400">Schedule</a>
                </nav>

                <div class="flex items-center gap-2">
                    @if ($instagramUrl)
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" aria-label="Instagram"
                           class="flex size-9 items-center justify-center rounded-full border border-slate-200 bg-white/70 text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zm0 10.162a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                            </svg>
                        </a>
                    @endif
                    @if ($twitterUrl)
                        <a href="{{ $twitterUrl }}" target="_blank" rel="noopener" aria-label="X (Twitter)"
                           class="flex size-9 items-center justify-center rounded-full border border-slate-200 bg-white/70 text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/>
                            </svg>
                        </a>
                    @endif
                    @if ($tiktokUrl)
                        <a href="{{ $tiktokUrl }}" target="_blank" rel="noopener" aria-label="TikTok"
                           class="flex size-9 items-center justify-center rounded-full border border-slate-200 bg-white/70 text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                            </svg>
                        </a>
                    @endif

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

        <main>
            <section id="home" class="relative flex min-h-svh items-center overflow-hidden bg-slate-950 bg-cover bg-center"
                     style="{{ $heroStyle }}">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-950/85 via-slate-950/70 to-violet-950/80"></div>

                <div class="relative z-10 mx-auto flex w-full max-w-3xl flex-col items-center px-4 py-24 text-center sm:px-6 lg:py-28">
                    <span class="mb-4 inline-flex w-fit rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-50">Official Fansite</span>
                    <h1 class="text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">
                        <span class="inline-block">Selamat Datang di Fansite</span>
                        <span class="mt-2 block text-yellow-300 [transform-style:preserve-3d] animate-[flip_1.4s_ease-in-out_1]">{{ $idolName }}</span>
                    </h1>
                    <p class="mt-5 max-w-xl text-base text-indigo-100 sm:text-lg">Temukan aktivitas terbaru, jadwal, dan momen favorit dari {{ $idolName }} dalam satu halaman yang selalu diperbarui.</p>

                    <div class="mt-8 flex flex-wrap justify-center gap-4">
                        <a href="#about" class="rounded-full bg-yellow-300 px-6 py-3 text-sm font-bold text-slate-900 shadow-lg shadow-yellow-200/50 transition hover:bg-yellow-200">Lihat Profil</a>
                        <a href="#schedule" class="rounded-full border border-white/40 bg-white/10 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/15">Jadwal Terbaru</a>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
                @if ($showOnWelcome)
                        <aside id="about" class="contents self-start lg:block">
                            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8 lg:sticky lg:top-24">
                                <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">About</p>
                                <h3 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Tentang {{ $idolName }}</h3>

                                <div class="mt-6">
                                    @if ($idolPhoto)
                                        <img src="{{ Storage::url($idolPhoto) }}" alt="{{ $idolName }}" class="h-[380px] w-full rounded-[1.5rem] object-cover shadow-lg shadow-indigo-200/50 dark:shadow-none" />
                                    @else
                                        <div class="flex h-[380px] w-full items-center justify-center rounded-[1.5rem] border border-slate-200 bg-slate-100 text-xl font-bold text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">{{ $idolName }}</div>
                                    @endif
                                </div>

                                <x-social-media-icons :instagram="$idolInstagramUrl" :twitter="$idolTwitterUrl" :tiktok="$idolTiktokUrl" class="mt-6 justify-center" />

                                <p class="mt-6 text-base leading-8 text-slate-600 dark:text-slate-300">{{ $idolDescription }}</p>

                                <a href="{{ $instagramUrl ?? '#' }}" target="{{ $instagramUrl ? '_blank' : '_self' }}" rel="noopener"
                                   class="mt-6 flex w-full items-center justify-center rounded-full bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
                                    Berkenalan dengan Oniel
                                </a>
                            </div>
                        </aside>
                    @endif

                <div class="flex flex-col gap-8">
                        <section id="data" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                            <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5 dark:border-slate-800">
                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Statistik</p>
                                    <h3 class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Data Oniel</h3>
                                </div>
                                <div class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300">
                                    Last Event: {{ $lastEventDate ? Carbon::parse($lastEventDate)->locale('id')->isoFormat('D MMMM YYYY') : '-' }}
                                </div>
                            </div>

                            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                                <div class="rounded-2xl bg-slate-100 p-5 dark:bg-slate-800/60">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Show Teater</p>
                                    <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ max(0, $showCount - $upcomingShowCount) }}</p>
                                    @if ($upcomingShowCount > 0)
                                        <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-900 dark:text-green-300">
                                            <span class="size-1.5 rounded-full bg-green-500 dark:bg-green-400"></span>
                                            {{ $upcomingShowCount }} Upcoming Show
                                        </span>
                                    @endif
                                </div>
                                <div class="rounded-2xl bg-indigo-50 p-5 dark:bg-indigo-950/60">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Single Participation</p>
                                    <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">12</p>
                                </div>
                                <div class="rounded-2xl bg-yellow-50 p-5 dark:bg-yellow-950/50">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Jumlah Setlist</p>
                                    <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $setlistCount }}</p>
                                </div>
                            </div>
                        </section>

                        <section id="schedule" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                            <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5 dark:border-slate-800">
                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Schedule</p>
                                    <h3 class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Event Mendatang</h3>
                                </div>
                            </div>

                            <div class="mt-6 space-y-4">
                                @forelse ($upcomingEvents as $event)
                                    @php
                                        $daysUntil = (int) now()->diffInDays(Carbon::parse($event['date']), false) + 1;
                                        $badgeColors = [
                                            'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
                                            'red' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
                                            'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
                                        ];
                                    @endphp
                                    <div class="flex items-center gap-4 rounded-2xl border border-green-100 bg-green-50 p-4 dark:border-green-900/40 dark:bg-green-950/20">
                                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeColors[$event['badge_color']] }}">
                                            {{ $event['type'] }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            @if (in_array($event['type'], ['Event', 'Meet & Greet'], true) && ! empty($event['purchase_link']))
                                                <a href="{{ $event['purchase_link'] }}" target="_blank" rel="noopener" class="line-clamp-2 text-base font-bold text-slate-900 dark:text-white">{{ $event['name'] }}</a>
                                            @else
                                                <p class="line-clamp-2 text-base font-bold text-slate-900 dark:text-white">{{ $event['name'] }}</p>
                                            @endif
                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ Carbon::parse($event['date'])->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                                        </div>
                                        <span class="shrink-0 text-xs font-medium text-green-600 dark:text-green-400">
                                            H-{{ $daysUntil }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">Tidak ada event mendatang yang terjadwal.</div>
                                @endforelse
                            </div>
                        </section>

                        <section id="live" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                            <div class="border-b border-slate-200 pb-5 dark:border-slate-800">
                                <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Live</p>
                                <h3 class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Status Live</h3>
                            </div>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-800/40">
                                    <div class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 text-base font-black text-white shadow-md">
                                        SR
                                    </div>
                                    <p class="text-base font-bold text-slate-900 dark:text-white">Showroom Live</p>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-200/70 px-3 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                        <span class="size-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                        Offline
                                    </span>
                                </div>
                                <div class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-800/40">
                                    <div class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-400 to-emerald-500 text-base font-black text-white shadow-md">
                                        IDN
                                    </div>
                                    <p class="text-base font-bold text-slate-900 dark:text-white">IDN App</p>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-200/70 px-3 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                        <span class="size-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                        Offline
                                    </span>
                                </div>
                            </div>
                        </section>
                    </div>


                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm text-slate-500 dark:text-slate-400 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                <div class="font-semibold text-slate-900 dark:text-white">{{ $appName }}</div>
                <p>© {{ now()->format('Y') }} {{ $appName }}</p>
            </div>
        </footer>
    </body>
</html>
