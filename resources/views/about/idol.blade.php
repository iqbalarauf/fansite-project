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
    @endphp

    <section class="bg-slate-950">
        <div class="bg-gradient-to-br from-indigo-950/85 via-slate-950/70 to-violet-950/80">
            <div class="mx-auto flex max-w-3xl flex-col items-center px-4 py-24 text-center sm:px-6 lg:py-28">
                <span class="mb-4 inline-flex w-fit rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-indigo-50">About Idol</span>
                <h1 class="text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">{{ $idolName }}</h1>
                @if ($idolDescription)
                    <p class="mt-5 max-w-xl text-base text-indigo-100 sm:text-lg">{{ $idolDescription }}</p>
                @endif
                <x-social-media-icons :instagram="$idolInstagramUrl" :twitter="$idolTwitterUrl" :tiktok="$idolTiktokUrl" class="mt-8 justify-center" />
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
            <div class="lg:sticky lg:top-24 lg:self-start">
                @if ($idolPhoto)
                    <img src="{{ Storage::url($idolPhoto) }}" alt="{{ $idolName }}" class="w-full rounded-[2rem] object-cover shadow-lg shadow-indigo-200/50 dark:shadow-none" />
                @else
                    <div class="flex aspect-[3/4] w-full items-center justify-center rounded-[2rem] border border-slate-200 bg-white text-2xl font-black text-indigo-600 dark:border-slate-800 dark:bg-slate-900 dark:text-indigo-400">{{ $idolName }}</div>
                @endif
            </div>

            <div class="flex flex-col gap-8">
                @if ($idolDescription)
                    <p class="text-lg leading-9 text-slate-600 dark:text-slate-300">{{ $idolDescription }}</p>
                @endif

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

                @if ($idolJikoshoukai)
                    <div class="rounded-[2rem] border border-indigo-200 bg-indigo-50 p-6 dark:border-indigo-900/50 dark:bg-indigo-950/40 sm:p-8">
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-400">Jikoshoukai</p>
                        <p class="mt-3 text-base leading-8 text-slate-700 dark:text-indigo-100">{{ $idolJikoshoukai }}</p>
                    </div>
                @endif

                @if (count($idolAchievements) > 0 || count($idolDiscography) > 0)
                    <div class="grid gap-4 md:grid-cols-2">
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
                @endif

                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('about.fansite') }}" class="rounded-full bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">Tentang Fansite</a>
                    <a href="{{ route('home') }}" class="rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-900 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:hover:text-indigo-400">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </section>
@endsection