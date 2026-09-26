@extends('layouts.public', ['title' => 'Schedule', 'active' => 'schedule'])

@section('content')
    @php
        use Illuminate\Support\Carbon;

        $badgeClasses = [
            'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
            'red' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
            'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
            'green' => 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
        ];
        $weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        {{-- Controls --}}
        <div class="flex flex-wrap items-center justify-between gap-4 lg:grid lg:grid-cols-3">
            <div class="inline-flex items-center rounded-full border border-slate-200 bg-white p-1 lg:justify-self-start dark:border-slate-700 dark:bg-slate-900">
                <button type="button" data-schedule-view="calendar" class="rounded-full px-4 py-1.5 text-sm font-semibold transition">Kalender</button>
                <button type="button" data-schedule-view="list" class="rounded-full px-4 py-1.5 text-sm font-semibold transition">List</button>
            </div>

            <div class="flex items-center justify-center gap-2 lg:justify-self-center">
                <a href="{{ route('schedule.index', ['month' => $prev['month'], 'year' => $prev['year'], 'view' => $initialView]) }}"
                   aria-label="Bulan sebelumnya"
                   class="flex size-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd" /></svg>
                </a>
                <h2 class="min-w-44 text-center text-xl font-black text-slate-900 dark:text-white sm:text-2xl">{{ $label }}</h2>
                <a href="{{ route('schedule.index', ['month' => $next['month'], 'year' => $next['year'], 'view' => $initialView]) }}"
                   aria-label="Bulan berikutnya"
                   class="flex size-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
                </a>
            </div>

            <form method="GET" action="{{ route('schedule.index') }}" class="flex items-center gap-2 lg:justify-self-end">
                <input type="hidden" name="view" value="{{ $initialView }}" />
                <select name="month" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>{{ Carbon::create(2000, $m, 1)->locale('id')->isoFormat('MMMM') }}</option>
                    @endfor
                </select>
                <select name="year" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    @foreach (range($year - 5, $year + 5) as $y)
                        <option value="{{ $y }}" {{ $y === $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-indigo-400">Terapkan</button>
            </form>
        </div>

        {{-- Legend --}}
        <div class="mt-6 flex flex-wrap gap-4 text-xs text-slate-500 dark:text-slate-400 items-center justify-center">
            @foreach (['Show Teater' => '#3b82f6', 'Event' => '#ef4444', 'Meet & Greet' => '#f97316', 'Live Streaming' => '#22c55e'] as $label => $color)
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block size-2.5 rounded-full" style="background: {{ $color }}"></span>
                    {{ $label }}
                </span>
            @endforeach
            <span class="inline-flex items-center gap-1.5">
                <span class="inline-block size-2.5 rounded-full bg-pink-500"></span>
                Ulang Tahun
            </span>
        </div>

        {{-- Calendar --}}
        <div id="schedule-calendar" class="mt-6">
            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <div class="min-w-[760px]">
                        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            @foreach ($weekdays as $weekday)
                                <div class="px-3 py-3 text-center">{{ $weekday }}</div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-7">
                            @foreach ($weeks as $week)
                                @foreach ($week as $day)
                                    <div class="min-h-[112px] border-b border-r border-slate-100 p-2 align-top dark:border-slate-800 {{ $day['in_month'] ? '' : 'bg-slate-50/70 dark:bg-slate-800/30' }} {{ $day['is_birthday'] ? 'bg-pink-50 dark:bg-pink-950/30' : '' }}">
                                        <div class="flex items-center justify-end gap-1">
                                            @if ($day['is_birthday'])
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="size-4 text-pink-500 dark:text-pink-400" role="img" aria-label="Ulang tahun {{ $idolName }}">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.055 4.024.165C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.379a48.474 48.474 0 00-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265zm-3 0a.375.375 0 11-.53 0L9 2.845l.265.265zm6 0a.375.375 0 11-.53 0L15 2.845l.265.265z"/>
                                                </svg>
                                            @endif
                                            <span class="flex size-6 items-center justify-center rounded-full text-xs font-semibold {{ $day['is_birthday'] ? 'bg-pink-500 text-white' : ($day['is_today'] ? 'bg-indigo-600 text-white' : ($day['in_month'] ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-600')) }}">{{ $day['day'] }}</span>
                                        </div>

                                        @if ($day['is_birthday'])
                                            <p class="mt-1 truncate text-[11px] font-semibold text-pink-600 dark:text-pink-400">Ulang Tahun {{ $idolName }}</p>
                                        @endif

                                        <div class="mt-1.5 space-y-1">
                                            @foreach ($day['events'] as $event)
                                                @if ($event['purchase_link'])
                                                    <a href="{{ $event['purchase_link'] }}" target="_blank" rel="noopener" title="{{ $event['type'] }}: {{ $event['name'] }}"
                                                       class="block truncate rounded-md px-1.5 py-1 text-[11px] font-medium {{ $badgeClasses[$event['badge']] }}">{{ $event['name'] }}</a>
                                                @else
                                                    <span title="{{ $event['type'] }}: {{ $event['name'] }}"
                                                          class="block truncate rounded-md px-1.5 py-1 text-[11px] font-medium {{ $badgeClasses[$event['badge']] }}">{{ $event['name'] }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- List --}}
        <div id="schedule-list" class="mt-6 hidden">
            @forelse ($events as $event)
                <div class="mb-3 flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="w-24 shrink-0 sm:w-28">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ Carbon::parse($event['date'])->locale('id')->isoFormat('D MMM YYYY') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ Carbon::parse($event['date'])->locale('id')->isoFormat('dddd') }}</p>
                        <span class="mt-1.5 inline-block whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClasses[$event['badge']] }}">{{ $event['type'] }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        @if ($event['purchase_link'])
                            <a href="{{ $event['purchase_link'] }}" target="_blank" rel="noopener" class="line-clamp-2 font-bold text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">{{ $event['name'] }}</a>
                        @else
                            <p class="line-clamp-2 font-bold text-slate-900 dark:text-white">{{ $event['name'] }}</p>
                        @endif
                        @if ($event['meta'])
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $event['meta'] }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-base font-semibold text-slate-700 dark:text-slate-200">Belum ada jadwal pada bulan ini.</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Coba pilih bulan lain.</p>
                </div>
            @endforelse
        </div>
    </section>

    <script>
        (function () {
            var buttons = document.querySelectorAll('[data-schedule-view]');
            var calendar = document.getElementById('schedule-calendar');
            var list = document.getElementById('schedule-list');

            function show(view) {
                if (!calendar || !list) {
                    return;
                }

                calendar.classList.toggle('hidden', view !== 'calendar');
                list.classList.toggle('hidden', view !== 'list');

                buttons.forEach(function (button) {
                    var active = button.dataset.scheduleView === view;
                    button.classList.toggle('bg-indigo-600', active);
                    button.classList.toggle('text-white', active);
                    button.classList.toggle('text-slate-600', !active);
                    button.classList.toggle('dark:text-slate-300', !active);
                });
            }

            buttons.forEach(function (button) {
                button.addEventListener('click', function () {
                    show(button.dataset.scheduleView);
                });
            });

            show('{{ $initialView }}');
        })();
    </script>
@endsection
