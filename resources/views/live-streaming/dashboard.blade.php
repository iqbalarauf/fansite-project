<x-layouts::app :title="__('Statistik Live')">
    @php
        $formatMinutes = function (?float $minutes): string {
            if ($minutes === null) {
                return '–';
            }

            $total = (int) round($minutes);
            $hours = intdiv($total, 60);
            $rest = $total % 60;

            return $hours > 0 ? $hours.'j '.$rest.'m' : $rest.'m';
        };

        $formatDecimal = fn (?float $value): string => $value === null ? '–' : number_format($value, 1, ',', '.');
        $formatInt = fn (?int $value): string => number_format((float) ($value ?? 0), 0, ',', '.');

        $periodOptions = [
            'all' => 'All',
            '7days' => '7 Hari',
            'monthly' => 'Bulanan',
            'quarter' => 'Kuartal',
            '6months' => '6 Bulan',
            'yearly' => '1 Tahun',
        ];

        $statCards = [
            [
                'label' => 'Total Live',
                'value' => $formatInt($totalLives),
                'sub' => $platformStats['IDN App']['count'].' IDN App · '.$platformStats['Showroom']['count'].' Showroom',
                'icon' => 'M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z',
                'color' => 'blue',
            ],
            [
                'label' => 'Total Durasi',
                'value' => $formatMinutes($totalDuration),
                'sub' => 'Akumulasi semua live',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'green',
            ],
            [
                'label' => 'Rata-rata Durasi',
                'value' => $formatMinutes($avgDuration),
                'sub' => 'Per sesi live',
                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
                'color' => 'purple',
            ],
            [
                'label' => 'Total Gift',
                'value' => $formatInt($totalGifts),
                'sub' => $formatDecimal($giftStats['avg']).' rata-rata per live',
                'icon' => 'M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
                'color' => 'pink',
            ],
            [
                'label' => 'Total Gold',
                'value' => $formatInt($totalGold),
                'sub' => 'Dari seluruh gift',
                'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'yellow',
            ],
        ];

        $colorMap = [
            'blue' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
            'green' => 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400',
            'purple' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
            'pink' => 'bg-pink-50 text-pink-600 dark:bg-pink-900/30 dark:text-pink-400',
            'yellow' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
        ];

        $maxDayCount = collect($dayStats['breakdown'])->max('count') ?: 0;
        $maxHourCount = collect($hourStats['ranked'])->max('count') ?: 0;
    @endphp

    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">Statistik Live Streaming</flux:heading>
                <flux:subheading>Ringkasan jumlah live, gift, dan penonton setia dari data live streaming.</flux:subheading>
            </div>

            <form method="GET" action="{{ route('live-streaming.dashboard') }}" class="flex flex-wrap items-center gap-2">
                <div class="flex items-center rounded-xl border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
                    @foreach ($periodOptions as $key => $label)
                        <button
                            type="submit"
                            name="period"
                            value="{{ $key }}"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors
                                {{ $period === $key
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100' }}"
                        >{{ $label }}</button>
                    @endforeach
                </div>
            </form>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium dark:border-zinc-700 dark:bg-zinc-800">
                <span class="text-zinc-400 dark:text-zinc-500">Periode:</span>
                {{ $periodLabel }}
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-300">
                {{ $periodFrom->locale('id')->isoFormat('D MMMM YYYY') }} – {{ $periodTo->locale('id')->isoFormat('D MMMM YYYY') }}
            </span>
        </div>

        {{-- Ringkasan utama --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($statCards as $card)
                <div class="flex flex-col gap-3 admin-card p-5">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg p-2 {{ $colorMap[$card['color']] }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $card['value'] }}</div>
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                        <div class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ $card['sub'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Per platform --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ($platformStats as $platform)
                <div class="admin-card">
                    <div class="admin-card-header">
                        <div class="flex items-center gap-2">
                            <span class="flex size-9 items-center justify-center rounded-lg {{ $platform['name'] === 'IDN App' ? 'bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                                </svg>
                            </span>
                            <div>
                                <div class="admin-card-title">{{ $platform['name'] }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $platform['count'] }} sesi live</div>
                            </div>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $formatMinutes($platform['duration']) }}
                        </span>
                    </div>

                    <div class="admin-card-body grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Total Live</div>
                            <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $formatInt($platform['count']) }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Rata-rata Durasi</div>
                            <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $formatMinutes($platform['avg_duration']) }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Total Gift</div>
                            <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $formatInt($platform['gift_total']) }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Rata-rata Gift</div>
                            <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $formatDecimal($platform['gift_avg']) }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Median Gift</div>
                            <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $formatDecimal($platform['gift_median']) }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Total Gold</div>
                            <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $formatInt($platform['gold_total']) }}</div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-700">
                        @if ($platform['top_gift'])
                            <div class="flex items-center gap-3">
                                @if ($platform['top_gift']['image_url'])
                                    <img src="{{ $platform['top_gift']['image_url'] }}" alt="{{ $platform['top_gift']['name'] }}" class="size-9 rounded-lg object-contain" loading="lazy">
                                @else
                                    <span class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-sm font-bold text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                                        {{ mb_substr($platform['top_gift']['name'], 0, 1) }}
                                    </span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $platform['top_gift']['name'] }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Gift paling sering · {{ $formatInt($platform['top_gift']['count']) }}× dikirim
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-zinc-400">Belum ada data gift untuk platform ini.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Leaderboard + insight --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="admin-card lg:col-span-2">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Leaderboard Top Gifters</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Gabungan dari top gifters setiap live</div>
                    </div>
                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        Top {{ count($leaderboard) }}
                    </span>
                </div>

                <div class="admin-card-body">
                    @forelse ($leaderboard as $index => $sender)
                        <div class="mb-2 flex items-center gap-3 rounded-lg border border-zinc-100 bg-zinc-50 px-3 py-2.5 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-bold
                                {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                {{ $index + 1 }}
                            </span>
                            @if (! empty($sender['avatar']))
                                <img src="{{ $sender['avatar'] }}" alt="{{ $sender['name'] }}" class="size-8 shrink-0 rounded-full object-cover" loading="lazy">
                            @else
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-semibold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ mb_substr($sender['name'], 0, 1) }}
                                </span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $sender['name'] }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $formatInt($sender['lives']) }}× masuk top gifter · terbaik {{ $formatInt($sender['best_gold']) }} gold
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-sm font-bold text-amber-600 dark:text-amber-400">{{ $formatInt($sender['total_gold']) }}</div>
                                <div class="text-[11px] text-zinc-400">total gold</div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 text-center text-zinc-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 size-8 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/>
                            </svg>
                            <p class="text-sm">Belum ada data top gifters dalam periode ini</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="admin-card p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="admin-card-title">Hari Paling Sering Live</div>
                        <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                            {{ $dayStats['top']['label'] ?? '–' }}
                        </span>
                    </div>

                    @if ($dayStats['top'])
                        <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $dayStats['top']['label'] }}
                            <span class="text-base font-normal text-zinc-500 dark:text-zinc-400">{{ $dayStats['top']['count'] }}× live</span>
                        </div>
                    @else
                        <p class="text-sm text-zinc-400">Belum ada data live.</p>
                    @endif

                    <div class="mt-4 flex flex-col gap-2">
                        @foreach ($dayStats['breakdown'] as $day)
                            <div class="flex items-center gap-2">
                                <span class="w-16 shrink-0 text-xs text-zinc-500 dark:text-zinc-400">{{ $day['label'] }}</span>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $maxDayCount > 0 ? round($day['count'] / $maxDayCount * 100) : 0 }}%"></div>
                                </div>
                                <span class="w-6 shrink-0 text-right text-xs font-medium text-zinc-600 dark:text-zinc-300">{{ $day['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="admin-card p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="admin-card-title">Jam Paling Sering Live</div>
                        <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            {{ $hourStats['top']['label'] ?? '–' }}
                        </span>
                    </div>

                    @if ($hourStats['top'])
                        <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $hourStats['top']['label'] }}
                            <span class="text-base font-normal text-zinc-500 dark:text-zinc-400">{{ $hourStats['top']['count'] }}× live</span>
                        </div>
                    @else
                        <p class="text-sm text-zinc-400">Belum ada data jam mulai live.</p>
                    @endif

                    <div class="mt-4 flex flex-col gap-2">
                        @forelse ($hourStats['ranked'] as $hour)
                            <div class="flex items-center gap-2">
                                <span class="w-12 shrink-0 text-xs text-zinc-500 dark:text-zinc-400">{{ $hour['label'] }}</span>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $maxHourCount > 0 ? round($hour['count'] / $maxHourCount * 100) : 0 }}%"></div>
                                </div>
                                <span class="w-6 shrink-0 text-right text-xs font-medium text-zinc-600 dark:text-zinc-300">{{ $hour['count'] }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-400">Belum ada data.</p>
                        @endforelse
                    </div>
                </div>

                <div class="admin-card p-5">
                    <div class="admin-card-title mb-3">Durasi Live</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Rata-rata</div>
                            <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $formatMinutes($durationStats['avg']) }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Median</div>
                            <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $formatMinutes($durationStats['median']) }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Terlama</div>
                            <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $formatMinutes($durationStats['max']) }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/50">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Tercepat</div>
                            <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ $formatMinutes($durationStats['min']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gift per platform --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ($platformStats as $platform)
                <div class="admin-card">
                    <div class="admin-card-header">
                        <div>
                            <div class="admin-card-title">Gift {{ $platform['name'] }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ count($platform['gifts']) }} jenis gift · {{ $formatInt($platform['gift_total']) }} total gift
                                @if (count($platform['gifts']) > 10)
                                    · scroll untuk lihat semua
                                @endif
                            </div>
                        </div>
                        @if ($platform['top_gift'])
                            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                Top: {{ $platform['top_gift']['name'] }}
                            </span>
                        @endif
                    </div>

                    <div class="admin-card-body">
                        @if (empty($platform['gifts']))
                            <div class="flex flex-col items-center justify-center py-10 text-center text-zinc-400">
                                <p class="text-sm">Belum ada gift yang tercatat untuk platform ini</p>
                            </div>
                        @else
                            {{-- Tampilkan 10 jenis sekaligus; sisanya dapat di-scroll. --}}
                            <div class="max-h-[620px] overflow-y-auto pr-1">
                                @foreach ($platform['gifts'] as $gift)
                                    <div class="mb-2 flex items-center gap-3 rounded-lg border border-zinc-100 bg-zinc-50 px-3 py-2.5 dark:border-zinc-800 dark:bg-zinc-800/50">
                                        @if (! empty($gift['image_url']))
                                            <img src="{{ $gift['image_url'] }}" alt="{{ $gift['name'] }}" class="size-8 shrink-0 rounded object-contain" loading="lazy">
                                        @else
                                            <span class="flex size-8 shrink-0 items-center justify-center rounded bg-amber-50 text-xs font-bold text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                                                {{ mb_substr($gift['name'], 0, 1) }}
                                            </span>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $gift['name'] }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $formatInt($gift['count']) }}× dikirim · {{ $gift['sessions'] }} sesi
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <div class="text-sm font-bold text-amber-600 dark:text-amber-400">{{ $formatInt($gift['total_gold']) }}</div>
                                            <div class="text-[11px] text-zinc-400">gold</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts::app>
