<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ShowTeaterModal from '@/Components/ShowTeaterModal.vue';
import { formatDateIndonesia } from '@/Helpers/formatDateIndonesia';

const props = defineProps({
    shows: Object,
    nextShowId: Number,
});

const showModal = ref(false);
const editingShow = ref(null);

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

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-end mb-6 gap-2">
                <div class="flex items-center">
                    <input type="checkbox"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600" />
                    <label for="is_global_center" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Use Scraping Data from Website</label>
                </div>
                <button :disabled='isDisabled'
                    class="inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    Sinkronisasi dari Sheet
                </button>
                <button @click="openAddModal"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Tambah Show
                </button>
            </div>

            <div v-if="shows.data && shows.data.length"
                class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-4 py-3">Show ID</th>
                                <th scope="col" class="px-4 py-3">Tanggal</th>
                                <th scope="col" class="px-4 py-3">Setlist</th>
                                <th scope="col" class="px-4 py-3">Unit Song</th>
                                <th scope="col" class="px-4 py-3">Global Center</th>
                                <th scope="col" class="px-4 py-3">US Center</th>
                                <th scope="col" class="px-4 py-3">Event</th>
                                <th scope="col" class="px-4 py-3">Info Tambahan</th>
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="show in shows.data" :key="show.show_id"
                                class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ show.show_id }}</td>
                                <td class="px-4 py-3">{{ formatDateIndonesia(show.show_date) }}</td>
                                <td class="px-4 py-3">{{ show.setlist }}</td>
                                <td class="px-4 py-3">{{ show.unit_song }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="show.is_global_center" class="text-green-600">✓</span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="show.is_us_center" class="text-green-600">✓</span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-3">{{ show.is_the_show_has_event || '-' }}</td>
                                <td class="px-4 py-3">{{ show.additional_information || '-' }}</td>
                                <td class="px-4 py-3">
                                    <button @click="openEditModal(show)"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <!-- Previous Button -->
                        <div>
                            <Link v-if="shows.prev_page_url" :href="shows.prev_page_url"
                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                            </Link>
                            <span v-else
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-400 dark:text-gray-600 uppercase tracking-widest cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                                Previous
                            </span>
                        </div>

                        <!-- Page Info -->
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">Halaman {{ shows.current_page }}</span>
                            <span class="mx-1">dari</span>
                            <span class="font-medium">{{ shows.last_page }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ shows.total }} data</span>
                        </div>

                        <!-- Next Button -->
                        <div>
                            <Link v-if="shows.next_page_url" :href="shows.next_page_url"
                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Next
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            </Link>
                            <span v-else
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-400 dark:text-gray-600 uppercase tracking-widest cursor-not-allowed">
                                Next
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-8 text-center">
                <p class="text-gray-500 dark:text-gray-400">Belum ada data show teater</p>
            </div>
        </div>

        <ShowTeaterModal :show="showModal" :editing-show="editingShow" :next-show-id="nextShowId" @close="closeModal" />
    </AppLayout>
</template>
