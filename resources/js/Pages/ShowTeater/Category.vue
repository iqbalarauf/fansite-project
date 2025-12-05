<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CategoryModal from '@/Components/CategoryModal.vue';

const props = defineProps({
    categories: Array,
    setlists: Array,
});

const showModal = ref(false);
const editingCategory = ref(null);
const deleting = ref(null);

const openAddModal = () => {
    editingCategory.value = null;
    showModal.value = true;
};

const openEditModal = (category) => {
    editingCategory.value = { ...category };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCategory.value = null;
};

const deleteCategory = (category) => {
    if (!confirm(`Are you sure you want to delete "${category.name}"?`)) return;

    deleting.value = category.id;

    router.delete(route('show-teater.categories.destroy', category.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
        onError: () => {
            deleting.value = null;
            alert('Failed to delete category. Please try again.');
        },
    });
};

// Search and filter functionality
const searchTerm = ref('');
const selectedType = ref('all');
const currentPage = ref(1);
const itemsPerPage = 10;

// Filtered categories based on search and filters
const filteredCategories = computed(() => {
    if (!props.categories) return [];

    let filtered = [...props.categories];

    // Apply search
    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        filtered = filtered.filter(cat =>
            cat.name?.toLowerCase().includes(term)
        );
    }

    // Apply type filter
    if (selectedType.value !== 'all') {
        filtered = filtered.filter(cat => cat.type === selectedType.value);
    }

    return filtered;
});

// Pagination for filtered data
const totalPages = computed(() => Math.ceil(filteredCategories.value.length / itemsPerPage));

const paginatedCategories = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredCategories.value.slice(start, end);
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
watch([searchTerm, selectedType], () => {
    currentPage.value = 1;
});
</script>

<template>

    <Head title="Setlist dan Unit Songs" />

    <AppLayout title="Setlist dan Unit Songs">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Master Data - Setlist dan Unit Songs
                </h2>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-end mb-6">
                <button @click="openAddModal"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Category
                </button>
            </div>

            <div v-if="categories && categories.length">
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <div
                        class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                        <div class="w-full md:w-1/2">
                            <form class="flex items-center" @submit.prevent>
                                <label for="search" class="sr-only">Search</label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                            fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input id="search" v-model="searchTerm" type="text"
                                        placeholder="Search category name..."
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                                </div>
                            </form>
                        </div>
                        <div
                            class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                            <div class="flex items-center space-x-3 w-full md:w-auto">
                                <select v-model="selectedType"
                                    class="w-full md:w-auto flex items-center justify-center py-2 px-6 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                                    <option value="all">All Types</option>
                                    <option value="setlist">Setlist</option>
                                    <option value="unit_song">Unit Song</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Type</th>
                                    <th scope="col" class="px-4 py-3">Setlist</th>
                                    <th scope="col" class="px-4 py-3">Name</th>
                                    <th scope="col" class="px-4 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="category in paginatedCategories" :key="category.id"
                                    class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full"
                                            :class="category.type === 'setlist' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'">
                                            {{ category.type === 'setlist' ? 'Setlist' : 'Unit Song' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span v-if="category.type === 'unit_song'" class="text-gray-600 dark:text-gray-400">
                                            {{ category.setlist_name || '-' }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-4 py-3">{{ category.name }}</td>
                                    <td class="px-4 py-3 flex items-center justify-end gap-3">
                                        <button @click="openEditModal(category)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Edit
                                        </button>
                                        <button @click="deleteCategory(category)"
                                            :disabled="deleting === category.id"
                                            :class="deleting === category.id ? 'text-red-300 cursor-not-allowed' : 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300'">
                                            <span v-if="deleting === category.id">Deleting...</span>
                                            <span v-else>Delete</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <nav v-if="filteredCategories.length > itemsPerPage"
                        class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4"
                        aria-label="Table navigation">
                        <ul class="inline-flex items-stretch -space-x-px">
                            <li>
                                <button :disabled="currentPage === 1" @click="prevPage"
                                    class="flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="sr-only">Previous</span>
                                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </li>
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400 px-3 py-1.5">
                                Showing
                                <span class="font-semibold text-gray-900 dark:text-white">{{ currentPage }}</span>
                                of
                                <span class="font-semibold text-gray-900 dark:text-white">{{ totalPages }}</span>
                            </span>
                            <li>
                                <button :disabled="currentPage === totalPages" @click="nextPage"
                                    class="flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="sr-only">Next</span>
                                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <div v-else class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-8 text-center">
                <p class="text-gray-500 dark:text-gray-400">Belum ada kategori</p>
            </div>
        </div>

        <CategoryModal :show="showModal" :category="editingCategory" :setlists="setlists" @close="closeModal" />
    </AppLayout>

</template>
