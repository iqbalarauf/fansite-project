<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import LiveStreamingModal from '@/Components/LiveStreamingModal.vue';
import { formatDateIndonesia } from '@/Helpers/formatDateIndonesia';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
    liveStreams: Array,
});

const showModal = ref(false);
const editingStream = ref(null);
const sortDirection = ref('asc');
const searchTerm = ref('');
const selectedPlatform = ref('all');
const selectedDateRange = ref(null);
const currentPage = ref(1);
const itemsPerPage = 10;

// Unique platforms from data
const platforms = computed(() => {
    if (!props.liveStreams) return [];
    return Array.from(new Set(
        props.liveStreams.map(s => s.platform).filter(Boolean)
    )).sort((a, b) => a.localeCompare(b));
});

const openAddModal = () => {
    editingStream.value = null;
    showModal.value = true;
};

const openEditModal = (stream) => {
    editingStream.value = { ...stream };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingStream.value = null;
};

const getPlatformBadgeClass = (platform) => {
    return platform === 'Showroom'
        ? 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
};

const filteredAndSortedStreams = computed(() => {
    if (!props.liveStreams) return [];

    let filtered = [...props.liveStreams];

    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        filtered = filtered.filter(stream =>
            stream.platform?.toLowerCase().includes(term) ||
            stream.additional_info?.toLowerCase().includes(term)
        );
    }

    // Apply platform filter
    if (selectedPlatform.value !== 'all') {
        filtered = filtered.filter(stream => stream.platform === selectedPlatform.value);
    }

    // Apply date range filter
    if (selectedDateRange.value) {
        const dates = Array.isArray(selectedDateRange.value) ? selectedDateRange.value : [selectedDateRange.value];
        const start = dates[0] ? new Date(dates[0]) : null;
        const end = dates[1] ? new Date(dates[1]) : dates[0] ? new Date(dates[0]) : null;
        if (end) end.setHours(23, 59, 59, 999);
        filtered = filtered.filter(stream => {
            const d = new Date(stream.live_date);
            if (start && d < start) return false;
            if (end && d > end) return false;
            return true;
        });
    }

    return filtered.sort((a, b) => {
        const aDate = a.live_date ? new Date(a.live_date) : new Date(0);
        const bDate = b.live_date ? new Date(b.live_date) : new Date(0);

        return sortDirection.value === 'asc'
            ? aDate - bDate
            : bDate - aDate;
    });
});

// Paginated streams
const totalPages = computed(() => Math.ceil(filteredAndSortedStreams.value.length / itemsPerPage));

const paginatedStreams = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredAndSortedStreams.value.slice(start, start + itemsPerPage);
});

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value -= 1;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value += 1;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

// Reset page when filters change
watch([searchTerm, selectedPlatform, selectedDateRange, sortDirection], () => {
    currentPage.value = 1;
});

</script>

<template>
    <AppLayout title="Live Streaming">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Live Streaming
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Header Section -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Live Streaming Data
                            </h3>
                            <button @click="openAddModal"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Add Live Stream
                            </button>
                        </div>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row gap-3 flex-wrap">
                            <div class="flex-1 min-w-[180px]">
                                <input v-model="searchTerm" type="text" placeholder="Search by platform or info..."
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div class="flex-shrink-0 w-48">
                                <VueDatePicker v-model="selectedDateRange" range placeholder="Select date range"
                                    format="dd/MM/yyyy" :clearable="true" :enable-time-picker="false" />
                            </div>
                            <div class="flex-shrink-0">
                                <select v-model="selectedPlatform"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="all">All Platforms</option>
                                    <option v-for="p in platforms" :key="p" :value="p">{{ p }}</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Urut:</span>
                                <button type="button" @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                                    class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 whitespace-nowrap">
                                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Platform</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Live Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Duration</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Additional Info</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="!paginatedStreams || paginatedStreams.length === 0">
                                    <td colspan="5"
                                        class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No live streaming data found.
                                    </td>
                                </tr>
                                <tr v-for="stream in paginatedStreams" :key="stream.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getPlatformBadgeClass(stream.platform)"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ stream.platform }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ formatDateIndonesia(stream.live_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ stream.duration ? `${stream.duration} min` : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                        <div class="max-w-xs truncate">
                                            {{ stream.additional_info || '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openEditModal(stream)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Section -->
                    <div v-if="filteredAndSortedStreams.length > itemsPerPage"
                        class="mt-4 px-2 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 rounded-b">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to
                                {{ Math.min(currentPage * itemsPerPage, filteredAndSortedStreams.length) }} of
                                {{ filteredAndSortedStreams.length }} results
                            </div>
                            <div class="flex gap-2">
                                <button @click="prevPage" :disabled="currentPage === 1"
                                    class="px-3 py-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Previous
                                </button>
                                <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300">
                                    Page {{ currentPage }} of {{ totalPages }}
                                </span>
                                <button @click="nextPage" :disabled="currentPage === totalPages"
                                    class="px-3 py-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <LiveStreamingModal :show="showModal" :editing-stream="editingStream" @close="closeModal" />
    </AppLayout>
</template>
