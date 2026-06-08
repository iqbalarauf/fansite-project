<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ShowTeaterModal from '@/Components/ShowTeaterModal.vue';
import { formatDateIndonesia } from '@/Helpers/formatDateIndonesia';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
    shows: Array,
    nextShowId: Number,
    allSetlists: Array,
    setlistsWithUnitSongs: Array,
    lastFetchAt: String,
});

const showModal = ref(false);
const editingShow = ref(null);
const isFetching = ref(false);
const processingShowId = ref(null);

const openAddModal = () => {
    editingShow.value = null;
    showModal.value = true;
};

const openEditModal = (show) => {
    editingShow.value = { ...show };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingShow.value = null;
};

// Search and filter functionality
const searchTerm = ref('');
const selectedSetlist = ref('all');
const selectedDateRange = ref(null);
const sortField = ref('show_date');
const sortDirection = ref('asc');
const currentPage = ref(1);
const itemsPerPage = 10;

// Use setlist values from show_teater data
const setlists = computed(() => {
    if (!props.shows) return [];

    return Array.from(new Set(
        props.shows
            .map(show => show.setlist)
            .filter(Boolean)
    )).sort((a, b) => a.localeCompare(b));
});

// Format last fetch date (use local time)
const formattedLastFetch = computed(() => {
    if (!props.lastFetchAt) return 'Belum pernah di-fetch';

    const date = new Date(props.lastFetchAt);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleString('id-ID', options);
});

// Filtered shows based on search, setlist and date range
const filteredShows = computed(() => {
    if (!props.shows) return [];

    let filtered = [...props.shows];

    // Apply search
    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        filtered = filtered.filter(show =>
            show.show_id?.toString().includes(term) ||
            show.setlist?.toLowerCase().includes(term) ||
            show.unit_song?.toLowerCase().includes(term) ||
            show.is_the_show_has_event?.toLowerCase().includes(term)
        );
    }

    // Apply setlist filter
    if (selectedSetlist.value !== 'all') {
        filtered = filtered.filter(show => show.setlist === selectedSetlist.value);
    }

    // Apply date range filter
    if (selectedDateRange.value) {
        const dates = Array.isArray(selectedDateRange.value) ? selectedDateRange.value : [selectedDateRange.value];
        const start = dates[0] ? new Date(dates[0]) : null;
        const end = dates[1] ? new Date(dates[1]) : dates[0] ? new Date(dates[0]) : null;

        filtered = filtered.filter(show => {
            const showDate = new Date(show.show_date);
            if (start && showDate < start) return false;
            if (end && showDate > end) return false;
            return true;
        });
    }

    return filtered;
});

const sortedShows = computed(() => {
    const sorted = [...filteredShows.value];

    sorted.sort((a, b) => {
        let aValue = a[sortField.value];
        let bValue = b[sortField.value];

        if (sortField.value === 'show_date') {
            aValue = new Date(aValue);
            bValue = new Date(bValue);
        } else {
            aValue = aValue ?? '';
            bValue = bValue ?? '';
        }

        if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1;
        return 0;
    });

    return sorted;
});

// Pagination for filtered and sorted data
const totalPages = computed(() => Math.ceil(sortedShows.value.length / itemsPerPage));

const paginatedShows = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return sortedShows.value.slice(start, end);
});

const goToFirstPage = () => {
    if (currentPage.value !== 1) {
        currentPage.value = 1;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

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

const goToLastPage = () => {
    if (currentPage.value !== totalPages.value) {
        currentPage.value = totalPages.value;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

watch([currentPage, totalPages], () => {
    const maxPage = Math.max(totalPages.value, 1);
    if (!currentPage.value || currentPage.value < 1) {
        currentPage.value = 1;
    } else if (currentPage.value > maxPage) {
        currentPage.value = maxPage;
    }
});

// Reset to page 1 when filters or sorting change
watch([searchTerm, selectedSetlist, selectedDateRange, sortField, sortDirection], () => {
    currentPage.value = 1;
});

// Fetch data manually
const fetchManually = async () => {
    if (isFetching.value) return;

    isFetching.value = true;
    try {
        const response = await fetch(route('show-teater.fetch-manual'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        const data = await response.json();

        if (data.success) {
            // Reload page to show updated data
            router.visit(route('show-teater.index'));
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat fetch data');
    } finally {
        isFetching.value = false;
    }
};

// Confirm member show
const confirmMemberShow = async (showId) => {
    processingShowId.value = showId;
    try {
        const response = await fetch(route('show-teater.confirm-member', showId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        const data = await response.json();

        if (data.success) {
            router.visit(route('show-teater.index'));
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat confirm show');
    } finally {
        processingShowId.value = null;
    }
};

// Reject member show
const rejectMemberShow = async (showId) => {
    if (!confirm('Apakah Anda yakin ingin menghapus show ini?')) return;

    processingShowId.value = showId;
    try {
        const response = await fetch(route('show-teater.reject-member', showId), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        const data = await response.json();

        if (data.success) {
            router.visit(route('show-teater.index'));
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat reject show');
    } finally {
        processingShowId.value = null;
    }
};
</script>

<template>

    <Head title="Master Data - Show Teater" />

    <AppLayout title="Show Teater">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Master Data - Show Teater
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-end mb-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400 text-right">
                        Last Fetch: {{ formattedLastFetch }}
                    </span>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Header Section -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Manage Show Teater
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Kelola jadwal pertunjukan teater
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex flex-col items-end gap-1">
                                    <button @click="fetchManually" :disabled="isFetching"
                                        class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-orange-600 text-white hover:bg-orange-700 focus:outline-none focus:bg-orange-700 disabled:opacity-50 disabled:pointer-events-none">
                                        <svg v-if="isFetching" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="size-4 animate-spin">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.995-1.465" />
                                        </svg>
                                        <span v-else>Fetch Data</span>
                                    </button>
                                </div>
                                <button @click="openAddModal"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                                    Tambahkan Show
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex gap-4 flex-nowrap">
                            <div class="flex-1">
                                <input v-model="searchTerm" type="text" placeholder="Search by setlist, unit song..."
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div class="flex-shrink-0 w-48">
                                <VueDatePicker
                                    v-model="selectedDateRange"
                                    range
                                    placeholder="Select date range"
                                    format="dd/MM/yyyy"
                                    :clearable="true"
                                    :enable-time-picker="false"
                                />
                            </div>
                            <div class="flex-shrink-0 w-40">
                                <select v-model="selectedSetlist"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="all">Semua Setlist</option>
                                    <option v-for="s in setlists" :key="s" :value="s">{{ s }}</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <select v-model="sortField"
                                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="show_date">Tanggal</option>
                                    <option value="show_id">Show ID</option>
                                    <option value="setlist">Setlist</option>
                                </select>
                                <button type="button" @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                                    class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 whitespace-nowrap">
                                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Show ID
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Setlist
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Unit Song
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Global Center
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        US Center
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Event
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Info Tambahan
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="paginatedShows.length === 0">
                                    <td colspan="9"
                                        class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No shows found
                                    </td>
                                </tr>
                                <tr v-for="show in paginatedShows" :key="show.show_id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{
                                        show.show_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ formatDateIndonesia(show.show_date) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ show.display_setlist || show.setlist }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ show.display_unit_song || show.unit_song }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <span v-if="show.is_global_center" class="text-green-600">✓</span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <span v-if="show.is_us_center" class="text-green-600">✓</span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ show.is_the_show_has_event || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ show.additional_information || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <!-- If is_member_show is NULL, show Yes/No buttons -->
                                        <div v-if="show.is_member_show === null" class="flex gap-2 justify-end">
                                            <button @click="confirmMemberShow(show.show_id)"
                                                :disabled="processingShowId === show.show_id"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                                title="Confirm as member show">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                            <button @click="rejectMemberShow(show.show_id)"
                                                :disabled="processingShowId === show.show_id"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                                title="Reject and delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- Otherwise show Edit button -->
                                        <button v-else @click="openEditModal(show)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Section -->
                    <div v-if="sortedShows.length > itemsPerPage"
                        class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to
                                {{ Math.min(currentPage * itemsPerPage, sortedShows.length) }} of
                                {{ sortedShows.length }} results
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-2">
                                <button @click="goToFirstPage" :disabled="currentPage === 1"
                                    class="px-3 py-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 1024 1024"
                                        version="1.1" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M785.493333 707.84L589.653333 512l195.84-195.84L725.333333 256 469.333333 512l256 256zM256 256h85.333333v512h-85.333333z" />
                                    </svg>
                                </button>
                                <button @click="prevPage" :disabled="currentPage === 1"
                                    class="px-3 py-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 1024 1024"
                                        version="1.1" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M657.706667 316.373333L597.333333 256 341.333333 512l256 256 60.373334-60.373333L462.08 512z" />
                                    </svg>
                                </button>
                                <div class="flex items-center gap-2">
                                    <label class="text-sm text-gray-700 dark:text-gray-300"
                                        for="page-input">Page</label>
                                    <input id="page-input" type="number" min="1" :max="totalPages"
                                        v-model.number="currentPage"
                                        class="w-12 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 px-2 py-1 focus:border-blue-500 focus:ring-blue-500" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">of {{ totalPages }}</span>
                                </div>
                                <button @click="nextPage" :disabled="currentPage === totalPages"
                                    class="px-3 py-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 1024 1024"
                                        version="1.1" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M426.666667 256l-60.373334 60.373333L561.92 512l-195.626667 195.626667L426.666667 768l256-256z" />
                                    </svg>
                                </button>
                                <button @click="goToLastPage" :disabled="currentPage === totalPages"
                                    class="px-3 py-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 1024 1024"
                                        version="1.1" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M238.506667 316.16L434.346667 512l-195.84 195.84L298.666667 768l256-256-256-256zM682.666667 256h85.333333v512h-85.333333z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <ShowTeaterModal :show="showModal" :editing-show="editingShow" :next-show-id="nextShowId"
            :setlists-with-unit-songs="setlistsWithUnitSongs" @close="closeModal" />
    </AppLayout>
</template>
