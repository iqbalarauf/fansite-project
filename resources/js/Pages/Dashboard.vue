<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, BarElement,
    Title, Tooltip, Legend,
} from 'chart.js';
import html2canvas from 'html2canvas';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
    idolBirthday: String,
    idolName: String,
    nextMilestone: Object,
    teaterStats: Object,
    allShowTeater: { type: Array, default: () => [] },
    allConcerts: { type: Array, default: () => [] },
    allMeetGreets: { type: Array, default: () => [] },
    allLiveStreamings: { type: Array, default: () => [] },
});

// ─── Reactive State ───────────────────────────────────────────────────────────
const selectedRange = ref('weekly');
const showComparison = ref(false);
const isCapturing = ref(false);
const dashboardRef = ref(null);
const now = ref(new Date());

let clockInterval = null;
onMounted(() => { clockInterval = setInterval(() => { now.value = new Date(); }, 60_000); });
onUnmounted(() => { if (clockInterval) clearInterval(clockInterval); });

// ─── Range Configuration ──────────────────────────────────────────────────────
const RANGES = {
    weekly: { label: '7 Hari', days: 7 },
    monthly: { label: 'Bulanan', days: 30 },
    quarter: { label: 'Kuartal', days: 90 },
    semiannual: { label: '6 Bulan', days: 180 },
    annual: { label: '1 Tahun', days: 365 },
};

// ─── Date Utilities ───────────────────────────────────────────────────────────
const parseDate = (s) => { const d = new Date(s); return isNaN(d) ? null : d; };
const dayStart = (d) => { const r = new Date(d); r.setHours(0, 0, 0, 0); return r; };
const dayEnd = (d) => { const r = new Date(d); r.setHours(23, 59, 59, 999); return r; };

const todayStart = computed(() => dayStart(now.value));

const curStart = computed(() => {
    const d = new Date(now.value);
    d.setDate(d.getDate() - RANGES[selectedRange.value].days + 1);
    return dayStart(d);
});
const curEnd = computed(() => dayEnd(now.value));

const prevEnd = computed(() => { const d = new Date(curStart.value); d.setDate(d.getDate() - 1); return dayEnd(d); });
const prevStart = computed(() => {
    const d = new Date(prevEnd.value);
    d.setDate(d.getDate() - RANGES[selectedRange.value].days + 1);
    return dayStart(d);
});

const filterPeriod = (items, field, start, end) =>
    items.filter(it => { const d = parseDate(it[field]); return d && d >= start && d <= end; });

// ─── Indonesian locale ────────────────────────────────────────────────────────
const IDN_MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const fmtDate = (s) => {
    const d = parseDate(s);
    return d ? `${d.getDate()} ${IDN_MONTHS[d.getMonth()]} ${d.getFullYear()}` : (s ?? '');
};

// ─── Chart Buckets ────────────────────────────────────────────────────────────
const chartBuckets = computed(() => {
    const n = now.value;
    const key = selectedRange.value;

    if (key === 'weekly') {
        return Array.from({ length: 7 }, (_, i) => {
            const d = new Date(n); d.setDate(d.getDate() - (6 - i));
            return {
                label: d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' }),
                start: dayStart(d), end: dayEnd(d),
            };
        });
    }

    if (key === 'monthly') {
        return Array.from({ length: 4 }, (_, i) => {
            const we = new Date(n); we.setDate(we.getDate() - (3 - i) * 7);
            const ws = new Date(we); ws.setDate(ws.getDate() - 6);
            return {
                label: `${ws.getDate()}/${ws.getMonth() + 1}–${we.getDate()}/${we.getMonth() + 1}`,
                start: dayStart(ws), end: dayEnd(we),
            };
        });
    }

    const numMonths = key === 'quarter' ? 3 : key === 'semiannual' ? 6 : 12;
    return Array.from({ length: numMonths }, (_, i) => {
        const d = new Date(n.getFullYear(), n.getMonth() - (numMonths - 1 - i), 1);
        return {
            label: d.toLocaleDateString('id-ID', { month: 'short', year: '2-digit' }),
            start: new Date(d.getFullYear(), d.getMonth(), 1, 0, 0, 0, 0),
            end: new Date(d.getFullYear(), d.getMonth() + 1, 0, 23, 59, 59, 999),
        };
    });
});

const bucketCounts = (items, field) =>
    chartBuckets.value.map(b => filterPeriod(items, field, b.start, b.end).length);

const chartData = computed(() => ({
    labels: chartBuckets.value.map(b => b.label),
    datasets: [
        { label: 'Show Teater', data: bucketCounts(props.allShowTeater, 'parsed_date'), backgroundColor: 'rgba(139,92,246,0.75)', borderColor: 'rgba(139,92,246,1)', borderWidth: 1, borderRadius: 4 },
        { label: 'Konser', data: bucketCounts(props.allConcerts, 'parsed_date'), backgroundColor: 'rgba(239,68,68,0.75)', borderColor: 'rgba(239,68,68,1)', borderWidth: 1, borderRadius: 4 },
        { label: 'Meet & Greet', data: bucketCounts(props.allMeetGreets, 'parsed_date'), backgroundColor: 'rgba(99,102,241,0.75)', borderColor: 'rgba(99,102,241,1)', borderWidth: 1, borderRadius: 4 },
        { label: 'Live Streaming', data: bucketCounts(props.allLiveStreamings, 'live_date'), backgroundColor: 'rgba(20,184,166,0.75)', borderColor: 'rgba(20,184,166,1)', borderWidth: 1, borderRadius: 4 },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', labels: { color: '#9ca3af', font: { size: 10 }, boxWidth: 12, padding: 8 } },
        tooltip: { mode: 'index', intersect: false },
    },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 9 }, maxRotation: 45 } },
        y: { beginAtZero: true, ticks: { color: '#9ca3af', stepSize: 1, precision: 0 }, grid: { color: 'rgba(156,163,175,0.1)' } },
    },
};

// ─── Live Streaming per Platform ──────────────────────────────────────────────
const liveStats = computed(() => {
    const inPeriod = filterPeriod(props.allLiveStreamings, 'live_date', curStart.value, curEnd.value);
    const map = {};
    inPeriod.forEach(s => { const p = s.platform || 'Lainnya'; map[p] = (map[p] || 0) + 1; });
    return Object.entries(map).map(([platform, count]) => ({ platform, count })).sort((a, b) => b.count - a.count);
});
const liveTotal = computed(() => liveStats.value.reduce((s, p) => s + p.count, 0));
const maxLiveCount = computed(() => liveStats.value.reduce((m, s) => Math.max(m, s.count), 1));

const PLATFORM_COLORS = {
    'Showroom': { bg: 'bg-pink-100 dark:bg-pink-900/30', text: 'text-pink-700 dark:text-pink-300', bar: 'bg-pink-500' },
    'IDN App': { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-700 dark:text-blue-300', bar: 'bg-blue-500' },
    'YouTube': { bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-700 dark:text-red-300', bar: 'bg-red-500' },
    'TikTok': { bg: 'bg-slate-100 dark:bg-slate-700', text: 'text-slate-700 dark:text-slate-200', bar: 'bg-slate-500' },
};
const platColor = (p) => PLATFORM_COLORS[p] || { bg: 'bg-gray-100 dark:bg-gray-700', text: 'text-gray-700 dark:text-gray-300', bar: 'bg-gray-400' };

// ─── Stat Card Comparison ─────────────────────────────────────────────────────
const periodStats = computed(() => {
    const calc = (start, end) => {
        const shows = filterPeriod(props.allShowTeater, 'parsed_date', start, end);
        return {
            shows: shows.length,
            setlists: new Set(shows.map(s => s.setlist).filter(Boolean)).size,
            unitSongs: new Set(shows.flatMap(s => s.unit_song ? s.unit_song.split(',').map(u => u.trim()) : [])).size,
            centerUS: shows.filter(s => s.is_us_center == 1).length,
            centerGlobal: shows.filter(s => s.is_global_center == 1).length,
        };
    };
    return { cur: calc(curStart.value, curEnd.value), prev: calc(prevStart.value, prevEnd.value) };
});

const absDelta = (cur, prev) => cur - prev;

// ─── Past & Upcoming Events ───────────────────────────────────────────────────
const pastEvents = computed(() => {
    const end = new Date(todayStart.value); end.setMilliseconds(-1);
    const start = curStart.value;
    return [
        ...filterPeriod(props.allShowTeater, 'parsed_date', start, end),
        ...filterPeriod(props.allConcerts, 'parsed_date', start, end),
        ...filterPeriod(props.allMeetGreets, 'parsed_date', start, end),
    ].sort((a, b) => (parseDate(b.parsed_date) || 0) - (parseDate(a.parsed_date) || 0));
});

const upcomingEvents = computed(() => {
    const start = todayStart.value;
    const end = new Date(now.value); end.setDate(end.getDate() + RANGES[selectedRange.value].days); end.setHours(23, 59, 59, 999);
    return [
        ...filterPeriod(props.allShowTeater, 'parsed_date', start, end),
        ...filterPeriod(props.allConcerts, 'parsed_date', start, end),
        ...filterPeriod(props.allMeetGreets, 'parsed_date', start, end),
    ].sort((a, b) => (parseDate(a.parsed_date) || 0) - (parseDate(b.parsed_date) || 0));
});

const daysUntil = (s) => {
    const d = parseDate(s); if (!d) return null;
    return Math.ceil((d - todayStart.value) / 86_400_000);
};

// ─── Birthday & Milestone ─────────────────────────────────────────────────────
const birthdayCD = computed(() => {
    if (!props.idolBirthday) return null;
    const birth = new Date(props.idolBirthday);
    const n = now.value;
    let next = new Date(n.getFullYear(), birth.getMonth(), birth.getDate());
    if (next <= n) next = new Date(n.getFullYear() + 1, birth.getMonth(), birth.getDate());
    return { days: Math.ceil((next - n) / 86_400_000) };
});

const milestoneCD = computed(() => {
    if (!props.nextMilestone) return null;
    const { showNumber, currentCount } = props.nextMilestone;
    return { remaining: showNumber - currentCount, showNumber, currentCount, pct: Math.min((currentCount / showNumber) * 100, 100) };
});

// ─── Screenshot ───────────────────────────────────────────────────────────────
const captureScreenshot = async () => {
    if (!dashboardRef.value || isCapturing.value) return;
    isCapturing.value = true;
    await nextTick();
    try {
        const isDark = document.documentElement.classList.contains('dark');
        const canvas = await html2canvas(dashboardRef.value, {
            scale: 1.8, useCORS: true, allowTaint: true, logging: false,
            backgroundColor: isDark ? '#111827' : '#f3f4f6',
        });
        const link = document.createElement('a');
        link.download = `dashboard-${new Date().toISOString().slice(0, 10)}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    } catch (e) { console.error('Screenshot error:', e); }
    finally { isCapturing.value = false; }
};
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div ref="dashboardRef" class="p-4 sm:p-6 lg:p-8 space-y-4 bg-gray-100 dark:bg-gray-900 min-h-screen">

            <!-- ══ CONTROLS BAR ══════════════════════════════════════════════════ -->
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between
                        bg-white dark:bg-gray-800 rounded-2xl px-5 py-3.5 shadow-sm
                        border border-gray-100 dark:border-gray-700">

                <!-- Range pills -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span
                        class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Periode</span>
                    <div class="flex gap-1.5 flex-wrap">
                        <button v-for="(cfg, key) in RANGES" :key="key" @click="selectedRange = key"
                            :class="selectedRange === key
                                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-indigo-900'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-600'"
                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-150 select-none">{{
                                cfg.label }}</button>
                    </div>
                </div>

                <!-- Right controls -->
                <div class="flex items-center gap-4 flex-wrap">
                    <!-- Comparison toggle -->
                    <label class="flex items-center gap-2 cursor-pointer select-none" for="cmp-toggle">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Perbandingan</span>
                        <div class="relative">
                            <input id="cmp-toggle" type="checkbox" v-model="showComparison" class="sr-only peer" />
                            <div
                                class="w-9 h-5 rounded-full bg-gray-300 dark:bg-gray-600 peer-checked:bg-indigo-500 transition-colors duration-200">
                            </div>
                            <div
                                class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-4">
                            </div>
                        </div>
                    </label>

                    <!-- Capture button -->
                    <button id="btn-capture" @click="captureScreenshot" :disabled="isCapturing" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700
                               disabled:opacity-60 text-white text-xs font-semibold rounded-lg shadow-sm
                               transition-all active:scale-95">
                        <svg v-if="!isCapturing" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
                        <svg v-else class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ isCapturing ? 'Proses…' : 'Capture' }}
                    </button>
                </div>
            </div>

            <!-- ══ ROW 1: STAT CARDS ═════════════════════════════════════════════ -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">

                <!-- Helper macro: each stat card -->
                <!-- 1. Jumlah Show -->
                <div id="stat-total-shows"
                    class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                            </svg>
                        </div>
                        <span v-if="showComparison"
                            :class="absDelta(periodStats.cur.shows, periodStats.prev.shows) >= 0 ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-red-500 bg-red-50 dark:bg-red-900/30 dark:text-red-400'"
                            class="text-xs font-black px-1.5 py-0.5 rounded-full">
                            {{ absDelta(periodStats.cur.shows, periodStats.prev.shows) > 0 ? '+' : '' }}{{
                                absDelta(periodStats.cur.shows, periodStats.prev.shows) }}
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white leading-none">{{
                        teaterStats?.total_shows ?? 0
                        }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Jumlah Show</div>
                    <div v-if="showComparison"
                        class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ periodStats.cur.shows }}</span>
                        ini
                        &middot; {{ periodStats.prev.shows }} lalu
                    </div>
                </div>

                <!-- 2. Setlist -->
                <div id="stat-setlists"
                    class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                        <span v-if="showComparison"
                            :class="absDelta(periodStats.cur.setlists, periodStats.prev.setlists) >= 0 ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-red-500 bg-red-50 dark:bg-red-900/30 dark:text-red-400'"
                            class="text-xs font-black px-1.5 py-0.5 rounded-full">
                            {{ absDelta(periodStats.cur.setlists, periodStats.prev.setlists) > 0 ? '+' : '' }}{{
                                absDelta(periodStats.cur.setlists, periodStats.prev.setlists) }}
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white leading-none">{{
                        teaterStats?.unique_setlists
                        ?? 0 }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Jumlah Setlist</div>
                    <div v-if="showComparison"
                        class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ periodStats.cur.setlists
                            }}</span> ini
                        &middot; {{ periodStats.prev.setlists }} lalu
                    </div>
                </div>

                <!-- 3. Unit Song -->
                <div id="stat-unit-songs"
                    class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                            </svg>
                        </div>
                        <span v-if="showComparison"
                            :class="absDelta(periodStats.cur.unitSongs, periodStats.prev.unitSongs) >= 0 ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-red-500 bg-red-50 dark:bg-red-900/30 dark:text-red-400'"
                            class="text-xs font-black px-1.5 py-0.5 rounded-full">
                            {{ absDelta(periodStats.cur.unitSongs, periodStats.prev.unitSongs) > 0 ? '+' : '' }}{{
                                absDelta(periodStats.cur.unitSongs, periodStats.prev.unitSongs) }}
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white leading-none">{{
                        teaterStats?.unique_unit_songs ?? 0 }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Jumlah Unit Song</div>
                    <div v-if="showComparison"
                        class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ periodStats.cur.unitSongs
                            }}</span> ini
                        &middot; {{ periodStats.prev.unitSongs }} lalu
                    </div>
                </div>

                <!-- 4. Center Unit Song -->
                <div id="stat-center-us"
                    class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-pink-100 dark:bg-pink-900/40 flex items-center justify-center">
                            <svg class="w-4 h-4 text-pink-600 dark:text-pink-400" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                            </svg>
                        </div>
                        <span v-if="showComparison"
                            :class="absDelta(periodStats.cur.centerUS, periodStats.prev.centerUS) >= 0 ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-red-500 bg-red-50 dark:bg-red-900/30 dark:text-red-400'"
                            class="text-xs font-black px-1.5 py-0.5 rounded-full">
                            {{ absDelta(periodStats.cur.centerUS, periodStats.prev.centerUS) > 0 ? '+' : '' }}{{
                                absDelta(periodStats.cur.centerUS, periodStats.prev.centerUS) }}
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white leading-none">{{
                        teaterStats?.us_center_count
                        ?? 0 }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Center Unit Song</div>
                    <div v-if="showComparison"
                        class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ periodStats.cur.centerUS
                            }}</span> ini
                        &middot; {{ periodStats.prev.centerUS }} lalu
                    </div>
                </div>

                <!-- 5. Global Center -->
                <div id="stat-global-center"
                    class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow col-span-2 sm:col-span-1">
                    <div class="flex items-start justify-between mb-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 1 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.166 7.757a.75.75 0 0 0 1.06-1.06l1.591-1.59a.75.75 0 0 1-1.061 1.06L6.166 7.76Z" />
                            </svg>
                        </div>
                        <span v-if="showComparison"
                            :class="absDelta(periodStats.cur.centerGlobal, periodStats.prev.centerGlobal) >= 0 ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-red-500 bg-red-50 dark:bg-red-900/30 dark:text-red-400'"
                            class="text-xs font-black px-1.5 py-0.5 rounded-full">
                            {{ absDelta(periodStats.cur.centerGlobal, periodStats.prev.centerGlobal) > 0 ? '+' : '' }}{{
                                absDelta(periodStats.cur.centerGlobal, periodStats.prev.centerGlobal) }}
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white leading-none">{{
                        teaterStats?.global_center_count ?? 0 }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Global Center</div>
                    <div v-if="showComparison"
                        class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ periodStats.cur.centerGlobal
                            }}</span>
                        ini &middot; {{ periodStats.prev.centerGlobal }} lalu
                    </div>
                </div>
            </div>

            <!-- ══ ROW 2: CHART · COUNTDOWN · LIVESTREAM ═════════════════════════ -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

                <!-- Col 1 — Activity Chart -->
                <div id="col-chart"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Oshimen Activity</h3>
                        <span
                            class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                            {{ RANGES[selectedRange].label }}
                        </span>
                    </div>
                    <div class="h-64">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Col 2 — Countdown -->
                <div id="col-countdown"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Countdown</h3>

                    <div class="space-y-4">
                        <!-- Birthday -->
                        <div v-if="birthdayCD" class="rounded-xl bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20
                                    border border-pink-200/70 dark:border-pink-800/50 p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-pink-500 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Zm-3 0a.375.375 0 1 1-.53 0L9 2.845l.265.265Zm6 0a.375.375 0 1 1-.53 0L15 2.845l.265.265Z" />
                                </svg>
                                <span class="text-xs font-bold text-pink-700 dark:text-pink-300 truncate">
                                    Birthday{{ idolName ? ` — ${idolName}` : '' }}
                                </span>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl font-black tabular-nums text-pink-600 dark:text-pink-400">{{
                                    birthdayCD.days }}</span>
                                <span class="text-sm font-semibold text-pink-500 dark:text-pink-400">hari lagi</span>
                            </div>
                            <!-- H-90 reminder -->
                            <div v-if="birthdayCD.days <= 90" class="mt-3 flex items-center gap-2 bg-yellow-50 dark:bg-yellow-900/20
                                        border border-yellow-200 dark:border-yellow-700/50 rounded-lg px-3 py-2">
                                <svg class="w-3.5 h-3.5 text-yellow-500 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                <span class="text-xs font-semibold text-yellow-700 dark:text-yellow-300">
                                    Reminder H-{{ birthdayCD.days }}! Siapkan project Seitansai!
                                </span>
                            </div>
                        </div>

                        <!-- Milestone -->
                        <div v-if="milestoneCD" class="rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-900/20 dark:to-violet-900/20
                                    border border-indigo-200/70 dark:border-indigo-800/50 p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                                </svg>
                                <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300">Milestone Show #{{
                                    milestoneCD.showNumber }}</span>
                            </div>
                            <div class="flex items-baseline gap-2 mb-2">
                                <span class="text-5xl font-black tabular-nums text-indigo-600 dark:text-indigo-400">{{
                                    milestoneCD.remaining }}</span>
                                <span class="text-sm font-semibold text-indigo-500 dark:text-indigo-400">show
                                    lagi</span>
                            </div>
                            <div class="text-xs text-indigo-400 mb-3">{{ milestoneCD.currentCount }} / {{
                                milestoneCD.showNumber
                                }} tercapai</div>
                            <!-- Progress bar -->
                            <div class="h-1.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-violet-500 rounded-full transition-all duration-700"
                                    :style="{ width: milestoneCD.pct + '%' }"></div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="!birthdayCD && !milestoneCD"
                            class="flex flex-col items-center justify-center py-12 text-gray-300 dark:text-gray-600">
                            <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span class="text-xs">Tidak ada data hitung mundur</span>
                        </div>
                    </div>
                </div>

                <!-- Col 3 — Live Streaming per Platform -->
                <div id="col-livestream"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Live Streaming</h3>
                        <span
                            class="text-xs font-semibold px-2 py-0.5 rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">
                            {{ RANGES[selectedRange].label }}
                        </span>
                    </div>

                    <div v-if="liveStats.length > 0" class="space-y-3.5">
                        <div v-for="stat in liveStats" :key="stat.platform" class="flex items-center gap-3">
                            <span :class="[platColor(stat.platform).bg, platColor(stat.platform).text]"
                                class="text-xs font-bold px-2 py-1 rounded-lg w-20 text-center flex-shrink-0 truncate">
                                {{ stat.platform }}
                            </span>
                            <div class="flex-1 h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div :class="platColor(stat.platform).bar"
                                    class="h-full rounded-full transition-all duration-700"
                                    :style="{ width: `${(stat.count / maxLiveCount) * 100}%` }">
                                </div>
                            </div>
                            <span
                                class="text-sm font-black text-gray-900 dark:text-white w-7 text-right flex-shrink-0 tabular-nums">{{
                                    stat.count }}</span>
                        </div>

                        <div
                            class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Total periode ini</span>
                            <span class="text-xl font-black text-gray-900 dark:text-white tabular-nums">{{ liveTotal
                                }}</span>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center h-44 text-gray-300 dark:text-gray-600">
                        <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <span class="text-xs">Belum ada live streaming dalam periode ini</span>
                    </div>
                </div>
            </div>

            <!-- ══ ROW 3: PAST & UPCOMING EVENTS ════════════════════════════════ -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <!-- Past Events -->
                <div id="col-past-events"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div
                        class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">Event Telah Berlalu</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full font-semibold">
                                {{ pastEvents.length }}
                            </span>
                            <span class="text-xs text-gray-400">{{ RANGES[selectedRange].label }}</span>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-50 dark:divide-gray-700/40 max-h-80 overflow-y-auto">
                        <div v-if="pastEvents.length === 0"
                            class="flex flex-col items-center justify-center py-12 text-gray-300 dark:text-gray-600">
                            <svg class="w-8 h-8 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />
                            </svg>
                            <span class="text-xs">Tidak ada event dalam periode ini</span>
                        </div>

                        <div v-for="event in pastEvents" :key="event.type + '-' + (event.show_id || event.id)"
                            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                            <!-- Badge -->
                            <span v-if="event.type === 'show_teater'"
                                class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300">
                                Show
                            </span>
                            <span v-else-if="event.type === 'concert'"
                                class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                Konser
                            </span>
                            <span v-else-if="event.type === 'meet_greet'"
                                class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                                {{ event.event_type === 'video-call' ? 'VC' : 'MG' }}
                            </span>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ event.setlist || event.event_name || '–' }}
                                </p>
                                <p class="text-xs text-gray-400">{{ fmtDate(event.parsed_date) }}</p>
                            </div>

                            <!-- Stars -->
                            <div class="flex gap-0.5 shrink-0">
                                <span v-if="event.is_global_center == 1" title="Global Center"
                                    class="text-amber-400 text-sm leading-none">⭐</span>
                                <span v-if="event.is_us_center == 1" title="Center Unit Song"
                                    class="text-pink-400 text-sm leading-none">★</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div id="col-upcoming-events"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div
                        class="flex items-center justify-between px-5 py-3.5 border-b border-emerald-100/60 dark:border-gray-700 bg-emerald-50/40 dark:bg-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">Event Mendatang</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded-full font-semibold">
                                {{ upcomingEvents.length }}
                            </span>
                            <span class="text-xs text-gray-400">{{ RANGES[selectedRange].label }}</span>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-50 dark:divide-gray-700/40 max-h-80 overflow-y-auto">
                        <div v-if="upcomingEvents.length === 0"
                            class="flex flex-col items-center justify-center py-12 text-gray-300 dark:text-gray-600">
                            <svg class="w-8 h-8 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />
                            </svg>
                            <span class="text-xs">Tidak ada event mendatang dalam periode ini</span>
                        </div>

                        <div v-for="event in upcomingEvents" :key="event.type + '-' + (event.show_id || event.id)"
                            class="flex items-center gap-3 px-5 py-3 hover:bg-emerald-50/30 dark:hover:bg-gray-700/40 transition-colors">
                            <!-- Badge -->
                            <span v-if="event.type === 'show_teater'"
                                class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300">
                                Show
                            </span>
                            <span v-else-if="event.type === 'concert'"
                                class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                Konser
                            </span>
                            <span v-else-if="event.type === 'meet_greet'"
                                class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                                {{ event.event_type === 'video-call' ? 'VC' : 'MG' }}
                            </span>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ event.setlist || event.event_name || '–' }}
                                </p>
                                <p class="text-xs text-gray-400">{{ fmtDate(event.parsed_date) }}</p>
                            </div>

                            <!-- Days until -->
                            <span class="shrink-0 text-xs font-bold tabular-nums
                                         text-emerald-600 dark:text-emerald-400
                                         bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded-full">
                                {{ daysUntil(event.parsed_date) === 0 ? 'Hari ini' : `+${daysUntil(event.parsed_date)}h`
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
