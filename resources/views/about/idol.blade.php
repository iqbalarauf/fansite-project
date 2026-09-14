@extends('layouts.public', ['title' => 'Tentang '.($idolName ?? 'Idol'), 'active' => 'idol'])

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
        use Illuminate\Support\Carbon;

        $details = array_values(array_filter([
            $idolBirthDate ? ['label' => 'Tanggal Lahir', 'value' => Carbon::parse($idolBirthDate)->locale('id')->isoFormat('D MMMM YYYY')] : null,
            $idolBirthPlace !== '' ? ['label' => 'Tempat Lahir', 'value' => $idolBirthPlace] : null,
            $idolBloodType !== '' ? ['label' => 'Golongan Darah', 'value' => $idolBloodType] : null,
            $idolHoroscope !== '' ? ['label' => 'Zodiak', 'value' => $idolHoroscope] : null,
        ]));

        $year = $theater['year'] ?? now()->year;
        $globalCenter = $theater['global_center'] ?? ['count_all' => 0, 'count_year' => 0, 'setlists_all' => [], 'setlists_year' => []];
        $usCenter = $theater['us_center'] ?? ['count_all' => 0, 'count_year' => 0, 'setlists_all' => [], 'setlists_year' => []];

        $handle = fn (?string $url): ?string => $url ? ltrim((string) parse_url($url, PHP_URL_PATH), '/') : null;
        $instagramHandle = $handle($idolInstagramUrl);
        $twitterHandle = $handle($idolTwitterUrl);
        $tiktokHandle = $handle($idolTiktokUrl);
    @endphp

    {{-- Profile --}}
    <section class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 lg:px-8">
        <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">About Idol</p>
        <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white sm:text-5xl">{{ $idolName }}</h1>
        @if ($idolDescription)
            <p class="mt-4 max-w-3xl text-lg leading-9 text-slate-600 dark:text-slate-300">{{ $idolDescription }}</p>
        @endif

        <a href="{{ route('timeline.index') }}"
           class="mt-6 inline-flex items-center gap-2 rounded-full bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Lihat Timeline
        </a>

        <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
            <div class="flex flex-col gap-6">
                @if ($idolPhoto)
                    <img src="{{ Storage::url($idolPhoto) }}" alt="{{ $idolName }}" class="w-full rounded-[2rem] object-cover shadow-lg shadow-indigo-200/50 dark:shadow-none" />
                @else
                    <div class="flex aspect-[3/4] w-full items-center justify-center rounded-[2rem] border border-slate-200 bg-white text-2xl font-black text-indigo-600 dark:border-slate-800 dark:bg-slate-900 dark:text-indigo-400">{{ $idolName }}</div>
                @endif

                <x-social-media-icons :instagram="$idolInstagramUrl" :twitter="$idolTwitterUrl" :tiktok="$idolTiktokUrl" class="justify-center" />
                @if ($idolJikoshoukai)
                    <div class="rounded-[2rem] border border-indigo-200 bg-indigo-50 p-6 dark:border-indigo-900/50 dark:bg-indigo-950/40 sm:p-8">
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Jikoshoukai</p>
                        <p class="mt-3 text-base leading-8 text-slate-700 dark:text-indigo-100">{{ $idolJikoshoukai }}</p>
                    </div>
                @endif
            </div>

            <div class="flex flex-col gap-8">
                @if (count($details) > 0)
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($details as $detail)
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $detail['label'] }}</p>
                                <p class="mt-2 text-xl font-black text-slate-900 dark:text-white">{{ $detail['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif



                @if (count($idolAchievements) > 0)
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">Pencapaian</h3>
                        <ul class="mt-5 space-y-3">
                            @foreach ($idolAchievements as $achievement)
                                <li class="flex items-start gap-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="mt-1.5 size-4 shrink-0 text-green-600 dark:text-green-400">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $achievement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (count($idolDiscography) > 0)
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">Diskografi</h3>
                        <ul class="mt-5 space-y-3">
                            @foreach ($idolDiscography as $discography)
                                <li class="flex items-start gap-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-1 size-4 shrink-0 text-indigo-600 dark:text-indigo-400">
                                        <path d="M9 18V5l12-2v13"/>
                                        <circle cx="6" cy="18" r="3"/>
                                        <circle cx="18" cy="16" r="3"/>
                                    </svg>
                                    <span>{{ $discography }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Show Teater --}}
    <section id="show-teater" class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Show Teater</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Setlist yang pernah dibawakan beserta jumlah penampilan.</p>

        @if (count($theater['setlists']) > 0)
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($theater['setlists'] as $setlist)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-black text-slate-900 dark:text-white">{{ $setlist['name'] }}</p>
                                @if ($setlist['jp_name'])
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $setlist['jp_name'] }}</p>
                                @endif
                            </div>
                            @if ($setlist['is_active'])
                                <span class="shrink-0 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900 dark:text-green-300">Setlist Aktif</span>
                            @endif
                        </div>
                        <p class="mt-4 text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $setlist['count'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">penampilan</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">Belum ada data show teater.</p>
        @endif
    </section>

    {{-- Unit Song --}}
    <section id="unit-song" class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Unit Song</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Unit song yang pernah dibawakan.</p>

        @if (count($theater['unit_songs']) > 0)
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($theater['unit_songs'] as $song)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-black text-slate-900 dark:text-white">{{ $song['name'] }}</p>
                                @if ($song['jp_name'])
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $song['jp_name'] }}</p>
                                @endif
                            </div>
                            @if ($song['on_going'])
                                <span class="shrink-0 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900 dark:text-amber-300">On Going</span>
                            @endif
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 p-3 text-center dark:bg-slate-800/50">
                                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $song['count_all'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">All</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 text-center dark:bg-slate-800/50">
                                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $song['count_year'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Tahun Ini</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">Belum ada data unit song.</p>
        @endif
    </section>

    {{-- Centers --}}
    <section class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <h3 class="text-2xl font-black text-slate-900 dark:text-white">Global Center</h3>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/50">
                        <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $globalCenter['count_all'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">All</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/50">
                        <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $globalCenter['count_year'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tahun Ini</p>
                    </div>
                </div>

                @if (count($globalCenter['setlists_all']) > 0)
                    <p class="mt-5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Setlist (All)</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($globalCenter['setlists_all'] as $name)
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $name }}</span>
                        @endforeach
                    </div>
                @endif

                @if (count($globalCenter['setlists_year']) > 0)
                    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Setlist ({{ $year }})</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($globalCenter['setlists_year'] as $name)
                            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">{{ $name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <h3 class="text-2xl font-black text-slate-900 dark:text-white">Center Unit Song</h3>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/50">
                        <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $usCenter['count_all'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">All</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/50">
                        <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $usCenter['count_year'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tahun Ini</p>
                    </div>
                </div>

                @if (count($usCenter['setlists_all']) > 0)
                    <p class="mt-5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Setlist (All)</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($usCenter['setlists_all'] as $name)
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $name }}</span>
                        @endforeach
                    </div>
                @endif

                @if (count($usCenter['setlists_year']) > 0)
                    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Setlist ({{ $year }})</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($usCenter['setlists_year'] as $name)
                            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">{{ $name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Social media embeds --}}
    @if ($idolInstagramUrl || $idolTwitterUrl || $idolTiktokUrl)
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">Sosial Media</h2>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                @if ($idolInstagramUrl)
                    <a href="{{ $idolInstagramUrl }}" target="_blank" rel="noopener"
                       class="flex flex-col items-center justify-center gap-3 rounded-[2rem] bg-gradient-to-br from-fuchsia-500 via-rose-500 to-amber-400 p-8 text-center text-white shadow-sm transition hover:opacity-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zm0 10.162a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                        </svg>
                        <p class="text-lg font-black">{{ '@'.$instagramHandle }}</p>
                        <span class="rounded-full bg-white/20 px-4 py-1 text-xs font-semibold">Follow di Instagram</span>
                    </a>
                @endif

                @if ($idolTwitterUrl)
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <a class="twitter-timeline" data-height="480" href="{{ $idolTwitterUrl }}">Tweets by {{ $twitterHandle }}</a>
                    </div>
                @endif

                @if ($idolTiktokUrl)
                    <a href="{{ $idolTiktokUrl }}" target="_blank" rel="noopener"
                       class="flex flex-col items-center justify-center gap-3 rounded-[2rem] bg-gradient-to-br from-slate-900 via-slate-800 to-black p-8 text-center text-white shadow-sm transition hover:opacity-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                        </svg>
                        <p class="text-lg font-black">{{ $tiktokHandle }}</p>
                        <span class="rounded-full bg-white/20 px-4 py-1 text-xs font-semibold">Follow di TikTok</span>
                    </a>
                @endif
            </div>
        </section>

        @if ($idolTwitterUrl)
            <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
        @endif
    @endif
@endsection
