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
const activeTab = ref('setlist');

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

// Search and pagination state
const searchTerm = ref('');
const sortDirection = ref('asc');
const currentPage = ref(1);
const itemsPerPage = 10;

const filteredSetlistCategories = computed(() => {
    if (!props.categories) return [];

    let filtered = props.categories.filter(cat => cat.type === 'setlist');

    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        filtered = filtered.filter(cat =>
            (cat.display_name || cat.name || '').toLowerCase().includes(term)
        );
    }

    return filtered;
});

const filteredUnitSongCategories = computed(() => {
    if (!props.categories) return [];

    let filtered = props.categories.filter(cat => cat.type === 'unit_song');

    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        filtered = filtered.filter(cat =>
            (cat.display_name || cat.name || '').toLowerCase().includes(term) ||
            (cat.setlist_name || '').toLowerCase().includes(term)
        );
    }

    return filtered;
});

const sortCategories = (categories) => {
    return [...categories].sort((a, b) => {
        const aValue = (a.display_name || a.name || '').toLowerCase();
        const bValue = (b.display_name || b.name || '').toLowerCase();

        if (aValue < bValue) {
            return sortDirection.value === 'asc' ? -1 : 1;
        }
        if (aValue > bValue) {
            return sortDirection.value === 'asc' ? 1 : -1;
        }
        return 0;
    });
};

const sortedSetlistCategories = computed(() => sortCategories(filteredSetlistCategories.value));
const sortedUnitSongCategories = computed(() => sortCategories(filteredUnitSongCategories.value));

const currentCategories = computed(() =>
    activeTab.value === 'setlist' ? sortedSetlistCategories.value : sortedUnitSongCategories.value
);

const totalPages = computed(() => Math.ceil(currentCategories.value.length / itemsPerPage));

const paginatedCategories = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return currentCategories.value.slice(start, start + itemsPerPage);
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

watch([searchTerm, activeTab, sortDirection], () => {
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Header Section -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Manage Setlist dan Unit Songs
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Kelola kategori setlist dan unit song
                                </p>
                            </div>
                            <button @click="openAddModal"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                                Add Category
                            </button>
                        </div>
                    </div>

                    <!-- Search and Tab Section -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" @click="activeTab = 'setlist'"
                                    :class="activeTab === 'setlist'
                                        ? 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white'
                                        : 'bg-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                                    class="px-4 py-2 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Setlist
                                </button>
                                <button type="button" @click="activeTab = 'unit_song'"
                                    :class="activeTab === 'unit_song'
                                        ? 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white'
                                        : 'bg-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                                    class="px-4 py-2 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Unit Song
                                </button>
                            </div>

                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-1">
                                    <input v-model="searchTerm" type="text"
                                        placeholder="Search by name or setlist..."
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                                        class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 whitespace-nowrap">
                                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                    </button>
                                </div>
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
                                        No
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {{ activeTab === 'setlist' ? 'Setlist Name' : 'Unit Song' }}
                                    </th>
                                    <th v-if="activeTab === 'unit_song'" scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Setlist
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="paginatedCategories.length === 0">
                                    <td colspan="4"
                                        class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No categories found
                                    </td>
                                </tr>
                                <tr v-for="(category, index) in paginatedCategories" :key="category.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ category.display_name || category.name }}
                                    </td>
                                    <td v-if="activeTab === 'unit_song'" class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ category.setlist_name || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openEditModal(category)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                            Edit
                                        </button>
                                        <button @click="deleteCategory(category)" :disabled="deleting === category.id"
                                            :class="deleting === category.id
                                                ? 'text-red-300 dark:text-red-700 cursor-not-allowed'
                                                : 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'">
                                            {{ deleting === category.id ? 'Deleting...' : 'Delete' }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Section -->
                    <div v-if="currentCategories.length > itemsPerPage"
                        class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Showing {{ currentCategories.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1 }} to
                                {{ Math.min(currentPage * itemsPerPage, currentCategories.length) }} of
                                {{ currentCategories.length }} results
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

        <CategoryModal :show="showModal" :category="editingCategory" :setlists="setlists" @close="closeModal" />
    </AppLayout>

</template>
