@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Carbon;

    $about = DB::table('about_settings')->pluck('value', 'key')->all();
    $appSettings = DB::table('app_settings')->pluck('value', 'key')->all();

    $idolName = $about['idol_name'] ?? 'Oshimen';
    $idolDescription = $about['idol_description'] ?? 'This is your idol profile.';
    $idolPhoto = $about['idol_photo'] ?? null;
    $instagramUrl = $about['instagram_url'] ?? $about['idol_social_media_instagram'] ?? null;
    $twitterUrl = $about['twitter_url'] ?? $about['idol_social_media_twitter'] ?? null;
    $tiktokUrl = $about['tiktok_url'] ?? $about['idol_social_media_tiktok'] ?? null;
    $heroImage = $appSettings['hero_image'] ?? null;
    $appLogo = $appSettings['app_logo'] ?? null;
    $sidebarName = $appSettings['sidebar_name'] ?? config('app.name', 'Laravel');
    $appName = $appSettings['app_name'] ?? config('app.name', 'Laravel');
    $showOnWelcome = filter_var($about['idol_show_on_welcome'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

    $showCount = DB::table('show_teater')->count();
    $liveStreamingCount = DB::table('live_streaming')->count();

    $upcomingEvents = collect()
        ->concat(DB::table('show_teater')->select('show_date as event_date', DB::raw("'Show Teater' as event_type"), 'setlist as event_name')->get()->map(fn ($item) => (array) $item))
        ->concat(DB::table('concert_events')->select('event_date', DB::raw("'Concert Event' as event_type"), 'event_name')->get()->map(fn ($item) => (array) $item))
        ->concat(DB::table('meet_greet_events')->select('event_date', DB::raw("'Meet & Greet' as event_type"), 'event_name')->get()->map(fn ($item) => (array) $item))
        ->filter(fn ($event) => ! empty($event['event_date']) && $event['event_date'] >= now()->toDateString())
        ->sortBy('event_date')
        ->take(5)
        ->values();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $appName }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 text-slate-800 antialiased">
        <header class="bg-white shadow-sm">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl text-lg font-bold text-white shadow-sm">
                        @if ($appLogo)
                            <img src="{{ Storage::url($appLogo) }}" alt="{{ $sidebarName }}" class="h-full w-full object-cover" />
                        @else
                            <span class="flex h-full w-full items-center justify-center rounded-xl bg-indigo-600 text-slate-900">{{ strtoupper(substr($sidebarName, 0, 1)) ?: 'F' }}</span>
                        @endif
                    </div>
                    <div class="text-xl font-black text-slate-900">{{ $sidebarName }}</div>
                </div>

                <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
                    <a href="#" class="hover:text-indigo-600">Home</a>
                    <a href="#about" class="hover:text-indigo-600">About</a>
                    <a href="#data" class="hover:text-indigo-600">Data</a>
                    <a href="#schedule" class="hover:text-indigo-600">Schedule</a>
                </nav>

                <div class="flex items-center gap-2">
                    @if ($instagramUrl)
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:border-indigo-300 hover:text-indigo-600">Instagram</a>
                    @endif
                    @if ($twitterUrl)
                        <a href="{{ $twitterUrl }}" target="_blank" rel="noopener" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:border-indigo-300 hover:text-indigo-600">X</a>
                    @endif
                    @if ($tiktokUrl)
                        <a href="{{ $tiktokUrl }}" target="_blank" rel="noopener" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:border-indigo-300 hover:text-indigo-600">TikTok</a>
                    @endif
                </div>
            </div>
        </header>

        <main>
            <section class="bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-500 text-white">
                <div class="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1.15fr_0.85fr] lg:px-8 lg:py-24">
                    <div class="flex flex-col justify-center">
                        <span class="mb-4 inline-flex w-fit rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-50">Official Fansite</span>
                        <h1 class="text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">
                            <span class="inline-block">Selamat Datang di Fansite</span>
                            <span class="mt-2 block text-yellow-300 [transform-style:preserve-3d] animate-[flip_1.4s_ease-in-out_1]">{{ $idolName }}</span>
                        </h1>
                        <p class="mt-5 max-w-xl text-base text-indigo-100 sm:text-lg">Temukan aktivitas terbaru, jadwal, dan momen favorit dari {{ $idolName }} dalam satu halaman yang selalu diperbarui.</p>

                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="#about" class="rounded-full bg-yellow-300 px-6 py-3 text-sm font-bold text-slate-900 shadow-lg shadow-yellow-200/50 transition hover:bg-yellow-200">Lihat Profil</a>
                            <a href="#schedule" class="rounded-full border border-white/40 bg-white/10 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/15">Jadwal Terbaru</a>
                        </div>
                    </div>

                    <div class="flex items-center justify-center">
                        @if ($heroImage)
                            <img src="{{ Storage::url($heroImage) }}" alt="{{ $idolName }}" class="h-[360px] w-full max-w-md rounded-[2rem] border border-white/20 object-cover shadow-2xl shadow-indigo-900/20" />
                        @else
                            <div class="flex h-[360px] w-full max-w-md items-center justify-center rounded-[2rem] border border-white/20 bg-white/10 text-5xl font-black text-white shadow-2xl shadow-indigo-900/20">{{ strtoupper(substr($idolName, 0, 1)) }}</div>
                        @endif
                    </div>
                </div>
            </section>

            @if ($showOnWelcome)
                <section id="about" class="mx-auto max-w-6xl px-4 py-20 sm:px-6 lg:px-8">
                    <div class="grid gap-8 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:grid-cols-2 lg:p-10">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600">About</p>
                            <h2 class="mt-3 text-3xl font-black text-slate-900">Tentang {{ $idolName }}</h2>
                            <p class="mt-5 text-base leading-8 text-slate-600">{{ $idolDescription }}</p>
                        </div>

                        <div class="flex justify-center">
                            @if ($idolPhoto)
                                <img src="{{ Storage::url($idolPhoto) }}" alt="{{ $idolName }}" class="h-[420px] w-full max-w-md rounded-[1.5rem] object-cover shadow-lg shadow-indigo-200" />
                            @else
                                <div class="flex h-[420px] w-full max-w-md items-center justify-center rounded-[1.5rem] border border-slate-200 bg-slate-100 text-xl font-bold text-indigo-600">{{ $idolName }}</div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            <section id="data" class="mx-auto max-w-6xl px-4 pb-20 sm:px-6 lg:px-8">
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600">Data</p>
                            <h3 class="mt-2 text-2xl font-black text-slate-900">Data Oniel</h3>
                        </div>
                        <div class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">Live update</div>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-slate-100 p-5">
                            <p class="text-sm text-slate-500">Jumlah Show</p>
                            <p class="mt-3 text-3xl font-black text-slate-900">{{ $showCount }}</p>
                        </div>
                        <div class="rounded-2xl bg-indigo-50 p-5">
                            <p class="text-sm text-slate-500">Live Streaming</p>
                            <p class="mt-3 text-3xl font-black text-slate-900">{{ $liveStreamingCount }}</p>
                        </div>
                        <div class="rounded-2xl bg-yellow-50 p-5">
                            <p class="text-sm text-slate-500">Jadwal</p>
                            <p class="mt-3 text-3xl font-black text-slate-900">{{ $upcomingEvents->count() }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="schedule" class="mx-auto max-w-6xl px-4 pb-20 sm:px-6 lg:px-8">
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600">Schedule</p>
                            <h3 class="mt-2 text-2xl font-black text-slate-900">Kegiatan Terbaru</h3>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($upcomingEvents as $event)
                            <div class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex min-w-[70px] flex-col items-center justify-center rounded-xl bg-indigo-100 px-2 py-3 text-center text-indigo-700">
                                    <span class="text-[10px] font-bold uppercase tracking-[0.12em]">{{ \Illuminate\Support\Carbon::parse($event['event_date'])->translatedFormat('M') }}</span>
                                    <span class="mt-1 text-2xl font-black">{{ \Illuminate\Support\Carbon::parse($event['event_date'])->format('d') }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-base font-bold text-slate-900">{{ $event['event_name'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $event['event_type'] }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">Belum ada jadwal kegiatan yang tersedia.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                <div class="font-semibold text-slate-900">{{ $appName }}</div>
                <p>© {{ now()->format('Y') }} {{ $appName }}</p>
            </div>
        </footer>
    </body>
</html>
