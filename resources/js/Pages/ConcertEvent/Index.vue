<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConcertEventModal from '@/Components/ConcertEventModal.vue';
import { formatDateIndonesia } from '@/Helpers/formatDateIndonesia';

const props = defineProps({
    events: Array,
});

const showModal = ref(false);
const editingEvent = ref(null);
const deleting = ref(null);

const openAddModal = () => {
    editingEvent.value = null;
    showModal.value = true;
};

const openEditModal = (event) => {
    editingEvent.value = { ...event };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingEvent.value = null;
};

const deleteEvent = (event) => {
    if (!confirm(`Are you sure you want to delete "${event.event_name}"?`)) return;

    deleting.value = event.id;

    router.delete(route('concert-events.destroy', event.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
        onError: () => {
            deleting.value = null;
            alert('Failed to delete event. Please try again.');
        },
    });
};

// Search and filter functionality
const searchTerm = ref('');
const selectedStatus = ref('all');
const currentPage = ref(1);
const itemsPerPage = 10;

// Filtered events based on search and filters
const filteredEvents = computed(() => {
    if (!props.events) return [];

    let filtered = [...props.events];

    // Apply search
    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        filtered = filtered.filter(event =>
            event.event_name?.toLowerCase().includes(term) ||
            event.location?.toLowerCase().includes(term)
        );
    }

    // Apply status filter
    if (selectedStatus.value !== 'all') {
        filtered = filtered.filter(event => event.status === selectedStatus.value);
    }

    return filtered;
});

// Pagination for filtered data
const totalPages = computed(() => Math.ceil(filteredEvents.value.length / itemsPerPage));

const paginatedEvents = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredEvents.value.slice(start, end);
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

// Reset to page 1 when filters change
watch([searchTerm, selectedStatus], () => {
    currentPage.value = 1;
});
</script>

<template>

    <Head title="Concert Events" />

    <AppLayout title="Concert Events">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Master Data - Concert Events
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Header Section -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Manage Concert Events
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Kelola data concert event di luar setlist teater
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button"
                                    class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-green-600 text-white hover:bg-green-700 focus:outline-none focus:bg-green-700 disabled:opacity-50 disabled:pointer-events-none">
                                    Sync with Sheet
                                </button>
                                <button @click="openAddModal"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                                    Add Concert Event
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <input v-model="searchTerm" type="text"
                                    placeholder="Search by event name or location..."
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <select v-model="selectedStatus"
                                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="all">All Status</option>
                                    <option value="off-air">Off-Air</option>
                                    <option value="on-air">On-Air</option>
                                </select>
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
                                        ID
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Event Name
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Location
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Purchase Link
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="paginatedEvents.length === 0">
                                    <td colspan="7"
                                        class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No concert events found
                                    </td>
                                </tr>
                                <tr v-for="event in paginatedEvents" :key="event.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ event.id }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ event.event_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ formatDateIndonesia(event.event_date) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ event.location }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            event.status === 'on-air'
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        ]">
                                            {{ event.status === 'on-air' ? 'On-Air' : 'Off-Air' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <a v-if="event.purchase_link" :href="event.purchase_link" target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            Link
                                        </a>
                                        <span v-else class="text-gray-400 dark:text-gray-600">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openEditModal(event)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                            Edit
                                        </button>
                                        <button @click="deleteEvent(event)" :disabled="deleting === event.id"
                                            :class="deleting === event.id
                                                ? 'text-red-300 dark:text-red-700 cursor-not-allowed'
                                                : 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'">
                                            {{ deleting === event.id ? 'Deleting...' : 'Delete' }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Section -->
                    <div v-if="filteredEvents.length > itemsPerPage"
                        class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to
                                {{ Math.min(currentPage * itemsPerPage, filteredEvents.length) }} of
                                {{ filteredEvents.length }} results
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

        <!-- Modal -->
        <ConcertEventModal :show="showModal" :editing-event="editingEvent" @close="closeModal" />
    </AppLayout>
</template>
