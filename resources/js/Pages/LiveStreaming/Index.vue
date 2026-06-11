<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import LiveStreamingModal from '@/Components/LiveStreamingModal.vue';
import { formatDateIndonesia } from '@/Helpers/formatDateIndonesia';

const props = defineProps({
    liveStreams: Array,
});

const showModal = ref(false);
const editingStream = ref(null);
const sortDirection = ref('asc');
const searchTerm = ref('');

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

    return filtered.sort((a, b) => {
        const aValue = a.platform?.toLowerCase() || '';
        const bValue = b.platform?.toLowerCase() || '';

        if (aValue < bValue) {
            return sortDirection.value === 'asc' ? -1 : 1;
        }
        if (aValue > bValue) {
            return sortDirection.value === 'asc' ? 1 : -1;
        }
        return 0;
    });
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
                    <div class="p-6 sm:p-8">
                        <div class="flex justify-between items-center mb-6">
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
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-col md:flex-row gap-4 md:items-center md:justify-between">
                            <div class="flex-1">
                                <input v-model="searchTerm" type="text" placeholder="Search by platform or info..."
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Urut:</span>
                                <button type="button" @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                                    class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 whitespace-nowrap">
                                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            ID</th>
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
                                    <tr v-if="!filteredAndSortedStreams || filteredAndSortedStreams.length === 0">
                                        <td colspan="6"
                                            class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No live streaming data found.
                                        </td>
                                    </tr>
                                    <tr v-for="stream in filteredAndSortedStreams" :key="stream.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            #{{ stream.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getPlatformBadgeClass(stream.platform)"
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ stream.platform }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ formatDateIndonesia(stream.live_date) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
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
                    </div>
                </div>
            </div>
        </div>

        <LiveStreamingModal :show="showModal" :editing-stream="editingStream" @close="closeModal" />
    </AppLayout>
</template>
