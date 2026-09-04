<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-6" id="dashboard-capture-area">

        {{-- ================================================================
             ROW 1: Header
        ================================================================ --}}
        <div class="flex flex-col gap-1">
            <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-white">
                Selamat Datang, {{ auth()->user()->name }} 👋
            </flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">
                Berikut adalah statistik oshimen: <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $idolName }}</span>
            </flux:subheading>
        </div>

        {{-- ================================================================
             ROW 2: Period Filter + Comparison + Capture
        ================================================================ --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            {{-- Period tabs --}}
            <form method="GET" action="{{ route('dashboard') }}" id="period-form" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="comparison" value="{{ $showComparison ? '1' : '0' }}" id="comparison-hidden" />
                <input type="hidden" name="period" value="{{ $period }}" id="period-hidden" />
                <input type="hidden" name="event_display_limit" value="{{ $eventDisplayLimit }}" id="period-event-display-limit-hidden" />
                <div class="flex items-center rounded-xl border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
                    @foreach ([
                        'all'    => 'All',
                        '7days'  => '7 Hari',
                        'monthly'=> 'Bulanan',
                        'quarter'=> 'Kuartal',
                        '6months'=> '6 Bulan',
                        'yearly' => '1 Tahun',
                    ] as $key => $label)
                        <button
                            type="submit"
                            name="period"
                            value="{{ $key }}"
                            onclick="document.getElementById('custom-date-from').removeAttribute('name'); document.getElementById('custom-date-to').removeAttribute('name');"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors
                                {{ $period === $key
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100' }}"
                        >{{ $label }}</button>
                    @endforeach
                </div>

                {{-- Custom Date Range --}}
                <div class="flex items-center gap-2">
                    <input
                        type="date"
                        name="date_from"
                        id="custom-date-from"
                        value="{{ $customFrom }}"
                        class="rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                    />
                    <span class="text-xs text-zinc-400">—</span>
                    <input
                        type="date"
                        name="date_to"
                        id="custom-date-to"
                        value="{{ $customTo }}"
                        class="rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-sm text-zinc-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300"
                    />
                    <button
                        type="submit"
                        name="period"
                        value="custom"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors
                            {{ $period === 'custom'
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 border border-zinc-200 dark:border-zinc-700' }}"
                    >Custom</button>
                </div>
            </form>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Comparison toggle --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Perbandingan</span>
                    <button
                        type="button"
                        id="comparison-toggle"
                        onclick="toggleComparison()"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                            {{ $showComparison ? 'bg-blue-600' : 'bg-zinc-300 dark:bg-zinc-600' }}"
                    >
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform
                            {{ $showComparison ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </div>

                <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-300">Jumlah event</span>
                    <div class="flex items-center gap-2">
                        @foreach ([5, 10, 15] as $limit)
                            <button
                                type="button"
                                onclick="setEventDisplayLimit({{ $limit }})"
                                class="flex size-8 items-center justify-center rounded-lg border text-xs font-medium transition-colors {{ $eventDisplayLimit === $limit ? 'border-blue-500 bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }}"
                            >{{ $limit }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Capture button --}}
                <button
                    type="button"
                    onclick="captureDashboard(this)"
                    class="flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Capture
                </button>
            </div>
        </div>

        {{-- ================================================================
             ROW 3: Stats Cards
        ================================================================ --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @php
                $statCards = [
                    ['label' => 'Jumlah Show', 'value' => $stats['total_shows'], 'prev' => $prevStats['total_shows'] ?? null, 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'blue'],
                    ['label' => 'Jumlah Setlist', 'value' => $stats['setlists'], 'prev' => $prevStats['setlists'] ?? null, 'icon' => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3', 'color' => 'purple'],
                    ['label' => 'Jumlah Unit Song', 'value' => $stats['unit_songs'], 'prev' => $prevStats['unit_songs'] ?? null, 'icon' => 'M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.661a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.661A2.25 2.25 0 009 15.553z', 'color' => 'pink'],
                    ['label' => 'Center Unit Song', 'value' => $stats['us_center'], 'prev' => $prevStats['us_center'] ?? null, 'icon' => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z', 'color' => 'yellow'],
                    ['label' => 'Global Center', 'value' => $stats['global_center'], 'prev' => $prevStats['global_center'] ?? null, 'icon' => 'M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418', 'color' => 'orange'],
                ];
                $colorMap = [
                    'blue'   => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                    'purple' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
                    'pink'   => 'bg-pink-50 text-pink-600 dark:bg-pink-900/30 dark:text-pink-400',
                    'yellow' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
                    'orange' => 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
                ];
            @endphp

            @foreach ($statCards as $card)
                @php
                    $diff = ($card['prev'] !== null) ? ($card['value'] - $card['prev']) : null;
                    $pct = ($card['prev'] > 0 && $diff !== null) ? round(abs($diff) / $card['prev'] * 100) : null;
                @endphp
                <div class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg p-2 {{ $colorMap[$card['color']] }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                            </svg>
                        </div>
                        @if ($showComparison && $diff !== null)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                {{ $diff > 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($diff < 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400') }}">
                                {{ $diff > 0 ? '+' : '' }}{{ $diff }}{{ $pct !== null ? " (" . $pct . "%)" : '' }}
                            </span>
                        @endif
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ number_format($card['value']) }}</div>
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                        @if ($showComparison && $card['prev'] !== null)
                            <div class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">vs {{ number_format($card['prev']) }} periode lalu</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ================================================================
             ROW 4: Activity Chart + Countdown + Live Streaming
        ================================================================ --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

            {{-- Activity Chart --}}
            <div class="col-span-1 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-1">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="sm" class="font-semibold">Oshimen Activity</flux:heading>
                    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                        {{ match($period) { 'all' => 'All', '7days' => '7 Hari', 'monthly' => 'Bulanan', 'quarter' => 'Kuartal', '6months' => '6 Bulan', 'yearly' => '1 Tahun', 'custom' => 'Custom' } }}
                    </span>
                </div>
                {{-- Legend --}}
                <div class="mb-3 flex flex-wrap gap-3 text-xs">
                    @foreach([['Show Teater','#3b82f6'],['Konser','#ef4444'],['Meet & Greet','#f97316'],['Live Streaming','#22c55e']] as [$lbl,$clr])
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-full" style="background:{{ $clr }}"></span>
                            {{ $lbl }}
                        </span>
                    @endforeach
                </div>
                <div class="relative h-48">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            {{-- Countdown + Milestone --}}
            <div class="col-span-1 flex flex-col gap-4">

                {{-- Birthday Countdown --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mb-3 flex items-center gap-2">
                        <div class="rounded-lg bg-pink-50 p-2 text-pink-500 dark:bg-pink-900/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            Birthday — {{ $idolName }}
                        </span>
                    </div>
                    @if ($birthdayCountdown !== null)
                        <div class="text-4xl font-bold text-pink-600 dark:text-pink-400">
                            {{ $birthdayCountdown }}
                            <span class="text-lg font-normal text-zinc-500 dark:text-zinc-400">hari lagi</span>
                        </div>
                        @if ($birthdayReminderActive)
                            <div class="mt-3 flex items-center gap-2 rounded-lg bg-yellow-50 px-3 py-2 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                </svg>
                                Reminder H-{{ $birthdayCountdown }}: Siapkan Project Seitansai!
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-zinc-400">Tanggal lahir belum diatur.</p>
                    @endif
                </div>

                {{-- Milestone Show --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mb-3 flex items-center gap-2">
                        <div class="rounded-lg bg-indigo-50 p-2 text-indigo-500 dark:bg-indigo-900/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Milestone Show {{ $nextMilestone }}</span>
                    </div>
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $milestoneRemaining }}
                        <span class="text-lg font-normal text-zinc-500 dark:text-zinc-400">show lagi</span>
                    </div>
                    <div class="mt-2 text-xs text-zinc-400">{{ $totalShows }} / {{ $nextMilestone }} tercapai</div>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div
                            class="h-2 rounded-full bg-indigo-500 transition-all duration-700"
                            style="width: {{ min(100, round($milestoneProgress / 100 * 100)) }}%"
                        ></div>
                    </div>
                </div>
            </div>

            {{-- Live Streaming --}}
            <div class="col-span-1 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="sm" class="font-semibold">Live Streaming</flux:heading>
                    <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        {{ match($period) { 'all' => 'All', '7days' => '7 Hari', 'monthly' => 'Bulanan', 'quarter' => 'Kuartal', '6months' => '6 Bulan', 'yearly' => '1 Tahun', 'custom' => 'Custom' } }}
                    </span>
                </div>
                @forelse ($liveStreamingEvents as $ls)
                    <div class="mb-3 flex items-center gap-3 rounded-lg border border-zinc-100 bg-zinc-50 px-3 py-2.5 dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="rounded-lg bg-green-100 p-2 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate">{{ $ls->platform }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ \Carbon\Carbon::parse($ls->live_date)->locale('id')->isoFormat('D MMMM YYYY') }}
                                @if ($ls->duration)
                                    · {{ $ls->duration }} menit
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 text-center text-zinc-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 size-8 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                        </svg>
                        <p class="text-sm">Belum ada live streaming dalam periode ini</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ================================================================
             ROW 5: Past Events + Upcoming Events
        ================================================================ --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- Past Events --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-zinc-400 dark:bg-zinc-500"></span>
                        <flux:heading size="sm" class="font-semibold">Event Telah Berlalu</flux:heading>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                        {{ $pastEvents->count() }} total
                    </span>
                </div>

                @forelse ($pastEvents as $event)
                    <div class="mb-2 flex items-center gap-3 rounded-lg border border-zinc-100 bg-zinc-50 px-3 py-2.5 dark:border-zinc-800 dark:bg-zinc-800/50">
                        @php
                            $badgeColors = ['blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'red' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'];
                        @endphp
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeColors[$event['badge_color']] }}">
                            {{ $event['type'] }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $event['name'] }}</div>
                            <div class="text-xs text-zinc-400">{{ \Carbon\Carbon::parse($event['date'])->locale('id')->isoFormat('D MMMM YYYY') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 text-center text-zinc-400">
                        <p class="text-sm">Tidak ada event yang telah berlalu dalam periode ini</p>
                    </div>
                @endforelse
            </div>

            {{-- Upcoming Events --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-green-500"></span>
                        <flux:heading size="sm" class="font-semibold">Event Mendatang</flux:heading>
                    </div>
                    <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        {{ $upcomingEvents->count() }} total
                    </span>
                </div>

                @forelse ($upcomingEvents as $event)
                    @php
                        $daysUntil = (int) now()->diffInDays(\Carbon\Carbon::parse($event['date']), false) +1;
                        $badgeColors = ['blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'red' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'];
                    @endphp
                    <div class="mb-2 flex items-center gap-3 rounded-lg border border-green-100 bg-green-50 px-3 py-2.5 dark:border-green-900/20 dark:bg-green-900/10">
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeColors[$event['badge_color']] }}">
                            {{ $event['type'] }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $event['name'] }}</div>
                            <div class="text-xs text-zinc-400">{{ \Carbon\Carbon::parse($event['date'])->locale('id')->isoFormat('D MMMM YYYY') }}</div>
                        </div>
                        <span class="shrink-0 text-xs font-medium text-green-600 dark:text-green-400">
                            H-{{ $daysUntil }}
                        </span>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 text-center text-zinc-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 size-8 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                        </svg>
                        <p class="text-sm">Tidak ada event mendatang yang terjadwal</p>
                    </div>
                @endforelse
            </div>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" id="event-controls-form" class="hidden">
            <input type="hidden" name="period" value="{{ $period }}" />
            <input type="hidden" name="comparison" value="{{ $showComparison ? '1' : '0' }}" />
            <input type="hidden" name="date_from" value="{{ $customFrom }}" />
            <input type="hidden" name="date_to" value="{{ $customTo }}" />
            <input type="hidden" name="event_display_limit" id="event-controls-display-limit-hidden" value="{{ $eventDisplayLimit }}" />
        </form>

    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
        // --- Activity Chart ---
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
        const labelColor = isDark ? '#a1a1aa' : '#71717a';

        new Chart(document.getElementById('activityChart'), {
            type: 'line',
            data: {
                labels: @json($chartDates),
                datasets: [
                    { label: 'Show Teater', data: @json($chartShowTeater), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 5 },
                    { label: 'Konser',      data: @json($chartKonser),     borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)',   tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 5 },
                    { label: 'Meet & Greet',data: @json($chartMeetGreet),  borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)',  tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 5 },
                    { label: 'Live Streaming', data: @json($chartLiveStreaming), borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.1)', tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 5 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: labelColor, maxTicksLimit: 7, maxRotation: 0 } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, stepSize: 1, precision: 0 }, min: 0 }
                }
            }
        });

        // --- Comparison toggle ---
        function toggleComparison() {
            const hidden = document.getElementById('comparison-hidden');
            const current = hidden.value === '1';
            hidden.value = current ? '0' : '1';
            document.getElementById('period-form').submit();
        }

        function setEventDisplayLimit(limit) {
            document.getElementById('event-controls-display-limit-hidden').value = limit;
            document.getElementById('event-controls-form').submit();
        }

        // --- Capture ---
        async function captureDashboard(button) {
            const el = document.getElementById('dashboard-capture-area');
            const originalButtonContent = button.innerHTML;
            button.disabled = true;
            button.textContent = 'Capturing...';
            const originals = [];

            try {
                if (typeof html2canvas !== 'function') {
                    throw new Error('Capture library is unavailable.');
                }

                el.querySelectorAll('canvas').forEach(canvas => {
                    const image = document.createElement('img');
                    image.src = canvas.toDataURL('image/png');
                    image.style.width = canvas.style.width || `${canvas.offsetWidth}px`;
                    image.style.height = canvas.style.height || `${canvas.offsetHeight}px`;
                    originals.push({ canvas, image, parent: canvas.parentNode });
                    canvas.parentNode.replaceChild(image, canvas);
                });

                const capturedCanvas = await html2canvas(el, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: isDark ? '#18181b' : '#ffffff',
                    logging: false,
                });
                const link = document.createElement('a');
                const now = new Date();
                const ts = now.getFullYear()
                    + String(now.getMonth() + 1).padStart(2, '0')
                    + String(now.getDate()).padStart(2, '0') + '-'
                    + String(now.getHours()).padStart(2, '0')
                    + String(now.getMinutes()).padStart(2, '0')
                    + String(now.getSeconds()).padStart(2, '0');
                link.download = `dashboard-${ts}.png`;
                link.href = capturedCanvas.toDataURL('image/png');
                link.click();
            } catch (error) {
                console.error('Capture failed:', error);
                alert('Gagal meng-capture dashboard.');
            } finally {
                originals.forEach(({ canvas, image, parent }) => {
                    parent.replaceChild(canvas, image);
                });
                button.disabled = false;
                button.innerHTML = originalButtonContent;
            }
        }
    </script>
</x-layouts::app>
