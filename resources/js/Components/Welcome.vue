<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    idolBirthday: String,
    idolName: String,
    nextMilestone: Object, // { showNumber: 300, currentCount: 250 }
    teaterStats: Object,   // { total_shows, unique_setlists, unique_unit_songs, us_center_count, global_center_count }
    lastWeekEvents: Array, // Last week's events (show_teater, concert, meet_greet)
    thisWeekEvents: Array, // This week's events (show_teater, concert, meet_greet)
});

const now = ref(new Date());
let interval = null;

onMounted(() => {
    interval = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});

// Birthday countdown (days only)
const birthdayCountdown = computed(() => {
    if (!props.idolBirthday) return null;

    const today = new Date(now.value);
    const birthDate = new Date(props.idolBirthday);

    // Get this year's birthday
    let nextBirthday = new Date(today.getFullYear(), birthDate.getMonth(), birthDate.getDate());

    // If birthday has passed this year, use next year
    if (nextBirthday < today) {
        nextBirthday = new Date(today.getFullYear() + 1, birthDate.getMonth(), birthDate.getDate());
    }

    const diff = nextBirthday - today;

    if (diff < 0) return null;

    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));

    return { days, date: nextBirthday };
});

// Show milestone countdown (shows remaining)
const milestoneCountdown = computed(() => {
    if (!props.nextMilestone) return null;

    const remaining = props.nextMilestone.showNumber - props.nextMilestone.currentCount;

    return { remaining };
});

// Indonesian date formatter
const formatIndonesianDate = (dateString) => {
    if (!dateString) return '';

    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;

    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    const day = date.getDate();
    const month = months[date.getMonth()];
    const year = date.getFullYear();

    return `${day} ${month} ${year}`;
};
</script>

<template>
    <div>
        <!-- Countdown Section -->
        <div v-if="birthdayCountdown || milestoneCountdown" class="bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 p-6 lg:p-8 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Birthday Countdown -->
                <div v-if="birthdayCountdown" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-pink-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Zm-3 0a.375.375 0 1 1-.53 0L9 2.845l.265.265Zm6 0a.375.375 0 1 1-.53 0L15 2.845l.265.265Z" />
                        </svg>
                        <h3 class="ms-3 text-xl font-bold text-gray-900 dark:text-white">
                            {{ idolName ? `${idolName}'s Birthday` : 'Birthday Countdown' }}
                        </h3>
                    </div>

                    <div class="flex flex-col items-center">
                        <div class="bg-pink-50 dark:bg-gray-700 rounded-lg p-8 text-center w-full">
                            <div class="text-6xl font-bold text-pink-600 dark:text-pink-400 mb-2">{{ birthdayCountdown.days }}</div>
                            <div class="text-lg text-gray-600 dark:text-gray-400">Days Until Birthday</div>
                        </div>

                        <!-- Seitansai Alert -->
                        <div v-if="birthdayCountdown.days <= 90" class="mt-4 bg-yellow-100 dark:bg-yellow-900/30 border-l-4 border-yellow-500 p-4 rounded w-full">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                <p class="ml-3 text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                                    Persiapkan Project Seitansai-nya!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Milestone Countdown -->
                <div v-if="milestoneCountdown" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-indigo-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                        </svg>
                        <div class="ms-3">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                Show #{{ nextMilestone.showNumber }} Milestone
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ nextMilestone.currentCount }} shows completed
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <div class="bg-indigo-50 dark:bg-gray-700 rounded-lg p-8 text-center">
                            <div class="text-6xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">{{ milestoneCountdown.remaining }}</div>
                            <div class="text-lg text-gray-600 dark:text-gray-400">Shows Remaining</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-200 dark:bg-gray-800 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
            <!-- Show Teater Statistics -->
            <div v-if="teaterStats" class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 md:col-span-2">
                <div class="flex items-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-purple-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 6.375a6.375 6.375 0 0 0-5.25 2.776m12.753-3.776h-3.385c-4.06 0-7.5-3.375-7.5-7.5s3.44-7.5 7.5-7.5m0 0H2.25m0 0v3.75C2.25 15.75 3.75 17.25 5.25 17.25h13.5A2.25 2.25 0 0 0 21 15v-3.75m0 0h3.75a.75.75 0 0 0 .75-.75V4.5a.75.75 0 0 0-.75-.75h-2.25A2.25 2.25 0 0 0 19.5 6.75" />
                    </svg>
                    <h3 class="ms-3 text-2xl font-bold text-gray-900 dark:text-white">
                        Show Teater Statistics
                    </h3>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <!-- Total Shows -->
                    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4 text-center border border-blue-200 dark:border-blue-800">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-1">{{ teaterStats.total_shows }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Jumlah Show</div>
                    </div>

                    <!-- Unique Setlists -->
                    <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-4 text-center border border-green-200 dark:border-green-800">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-1">{{ teaterStats.unique_setlists }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Jumlah Setlist</div>
                    </div>

                    <!-- Unique Unit Songs -->
                    <div class="bg-orange-50 dark:bg-orange-900/30 rounded-lg p-4 text-center border border-orange-200 dark:border-orange-800">
                        <div class="text-3xl font-bold text-orange-600 dark:text-orange-400 mb-1">{{ teaterStats.unique_unit_songs }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Jumlah Unit Song</div>
                    </div>

                    <!-- US Center Count -->
                    <div class="bg-pink-50 dark:bg-pink-900/30 rounded-lg p-4 text-center border border-pink-200 dark:border-pink-800">
                        <div class="text-3xl font-bold text-pink-600 dark:text-pink-400 mb-1">{{ teaterStats.us_center_count }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Center Unit Song</div>
                    </div>

                    <!-- Global Center Count -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-lg p-4 text-center border border-yellow-200 dark:border-yellow-800">
                        <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mb-1">{{ teaterStats.global_center_count }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Global Center</div>
                    </div>
                </div>
            </div>

            <!-- This Week's Shows -->
            <div v-if="thisWeekEvents && thisWeekEvents.length > 0" class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 md:col-span-2">
                <div class="flex items-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-blue-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />
                    </svg>
                    <h3 class="ms-3 text-2xl font-bold text-gray-900 dark:text-white">
                        This Week's Upcoming Events
                    </h3>
                </div>

                <div class="space-y-3">
                    <div v-for="event in thisWeekEvents" :key="event.type + '-' + (event.show_id || event.id)" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <!-- Show Teater Event -->
                        <div v-if="event.type === 'show_teater'" class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3 mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                                    </svg>
                                    Show Teater
                                </span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ event.setlist }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatIndonesianDate(event.show_date) }}</span>
                            </div>
                            <div v-if="event.unit_song" class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Unit Song:</span> {{ event.unit_song }}
                            </div>
                        </div>

                        <!-- Concert Event -->
                        <div v-else-if="event.type === 'concert'" class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3 mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                    Concert
                                </span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ event.event_name }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatIndonesianDate(event.event_date) }}</span>
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Location:</span> {{ event.location }}
                            </div>
                        </div>

                        <!-- Meet & Greet Event -->
                        <div v-else-if="event.type === 'meet_greet'" class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3 mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                    </svg>
                                    {{ event.event_type === 'video-call' ? 'Video Call' : 'Meet & Greet' }}
                                </span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ event.event_name }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatIndonesianDate(event.event_date) }}</span>
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Location:</span> {{ event.location }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Last Week's Shows -->
            <div v-if="lastWeekEvents && lastWeekEvents.length > 0" class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 md:col-span-2">
                <div class="flex items-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-green-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <h3 class="ms-3 text-2xl font-bold text-gray-900 dark:text-white">
                        Last Week's Schedules
                    </h3>
                </div>

                <div class="space-y-4">
                    <div v-for="event in lastWeekEvents" :key="event.type + '-' + (event.show_id || event.id)" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <!-- Show Teater Event -->
                        <div v-if="event.type === 'show_teater'" class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-4 mb-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3 mr-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                                        </svg>
                                        Show Teater
                                    </span>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ event.setlist }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatIndonesianDate(event.show_date) }}</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                                    <div v-if="event.unit_song" class="flex items-center gap-2">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Unit Song:</span>
                                        <span class="text-gray-900 dark:text-white">{{ event.unit_song }}</span>
                                    </div>

                                    <div v-if="event.is_us_center == 1" class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-pink-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                        </svg>
                                        <span class="font-medium text-pink-600 dark:text-pink-400">Center Unit Song</span>
                                    </div>

                                    <div v-if="event.is_global_center == 1" class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-yellow-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 1-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 1 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.166 7.757a.75.75 0 0 0 1.06-1.06l1.591-1.59a.75.75 0 0 1-1.061 1.06L6.166 7.76Z" />
                                        </svg>
                                        <span class="font-medium text-yellow-600 dark:text-yellow-400">Global Center</span>
                                    </div>

                                    <div v-if="event.is_the_show_has_event" class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-blue-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m15.817 2.58a6 6 0 0 1-7.38 5.84h-4.8m4.8-5.84a14.926 14.926 0 0 1 5.84-2.58m2.58 5.84a6 6 0 0 1-5.84 7.38v-4.8m-5.84-2.58a14.927 14.927 0 0 0-5.84 2.58m-2.58-5.84a6 6 0 0 1 5.84-7.38v4.8m5.84 2.58a14.927 14.927 0 0 1 2.58-5.84" />
                                        </svg>
                                        <span class="font-medium text-blue-600 dark:text-blue-400">Event</span>
                                    </div>
                                </div>

                                <div v-if="event.additional_information" class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-md border border-blue-200 dark:border-blue-800">
                                    <div class="flex items-start gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-blue-500 mt-0.5 flex-shrink-0">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                        </svg>
                                        <div class="text-sm text-blue-800 dark:text-blue-200">
                                            <strong>Info Tambahan:</strong> {{ event.additional_information }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Concert Event -->
                        <div v-else-if="event.type === 'concert'" class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-4 mb-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3 mr-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                        Concert
                                    </span>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ event.event_name }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatIndonesianDate(event.event_date) }}</span>
                                </div>

                                <div class="flex items-center gap-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-gray-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.458-7.5 11.458s-7.5-4.316-7.5-11.458a7.5 7.5 0 1 1 15 0Z" />
                                        </svg>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Location:</span>
                                        <span class="text-gray-900 dark:text-white">{{ event.location }}</span>
                                    </div>

                                    <div v-if="event.status === 'on-air'" class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3 mr-1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 0 1 0 1.971l-11.54 6.347a1.125 1.125 0 0 1-1.667-.985V5.653Z" />
                                            </svg>
                                            On Air
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Meet & Greet Event -->
                        <div v-else-if="event.type === 'meet_greet'" class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-4 mb-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3 mr-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                        </svg>
                                        {{ event.event_type === 'video-call' ? 'Video Call' : 'Meet & Greet' }}
                                    </span>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ event.event_name }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatIndonesianDate(event.event_date) }}</span>
                                </div>

                                <div class="flex items-center gap-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-gray-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.458-7.5 11.458s-7.5-4.316-7.5-11.458a7.5 7.5 0 1 1 15 0Z" />
                                        </svg>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Location:</span>
                                        <span class="text-gray-900 dark:text-white">{{ event.location }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
