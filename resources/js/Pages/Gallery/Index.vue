<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GalleryModal from '@/Components/GalleryModal.vue';

const props = defineProps({
    galleries: Array,
});

const showModal = ref(false);
const editingItem = ref(null);

const openAddModal = () => {
    editingItem.value = null;
    showModal.value = true;
};

const openEditModal = (item) => {
    editingItem.value = { ...item };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingItem.value = null;
};

// Extract YouTube video ID from URL
const getYouTubeVideoId = (url) => {
    if (!url) return null;
    const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
    return match ? match[1] : null;
};
</script>

<template>
    <AppLayout title="Gallery">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gallery
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:p-8">
                        <!-- Header Actions -->
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Gallery Items</h3>
                            <button
                                @click="openAddModal"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add New Item
                            </button>
                        </div>

                        <!-- Gallery Grid -->
                        <div v-if="galleries && galleries.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="item in galleries"
                                :key="item.id"
                                class="group relative bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">

                                <!-- Image/Video Preview -->
                                <div class="aspect-video bg-gray-200 dark:bg-gray-600 relative overflow-hidden">
                                    <!-- Photo Type -->
                                    <img
                                        v-if="item.type === 'photo' && item.image_path"
                                        :src="item.image_path"
                                        :alt="item.title"
                                        class="w-full h-full object-cover">

                                    <!-- Video Type -->
                                    <div v-else-if="item.type === 'video' && item.video_url" class="relative w-full h-full">
                                        <img
                                            :src="`https://img.youtube.com/vi/${getYouTubeVideoId(item.video_url)}/maxresdefault.jpg`"
                                            :alt="item.title"
                                            class="w-full h-full object-cover">
                                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30">
                                            <svg class="w-16 h-16 text-white opacity-80" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Type Badge -->
                                    <div class="absolute top-2 right-2">
                                        <span :class="[
                                            'px-2 py-1 text-xs font-medium rounded',
                                            item.type === 'photo' ? 'bg-blue-500 text-white' : 'bg-red-500 text-white'
                                        ]">
                                            {{ item.type === 'photo' ? 'Photo' : 'Video' }}
                                        </span>
                                    </div>

                                    <!-- Published Status -->
                                    <div v-if="!item.is_published" class="absolute top-2 left-2">
                                        <span class="px-2 py-1 text-xs font-medium rounded bg-gray-500 text-white">
                                            Unpublished
                                        </span>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">{{ item.title }}</h4>
                                    <p v-if="item.description" class="text-sm text-gray-600 dark:text-gray-300 mb-2 line-clamp-2">{{ item.description }}</p>
                                    <p v-if="item.credit" class="text-xs text-gray-500 dark:text-gray-400 italic">Credit: {{ item.credit }}</p>

                                    <!-- Actions -->
                                    <div class="mt-4 flex gap-2">
                                        <button
                                            @click="openEditModal(item)"
                                            class="flex-1 px-3 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                                            Edit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No gallery items</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new gallery item.</p>
                            <div class="mt-6">
                                <button
                                    @click="openAddModal"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add New Item
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <GalleryModal
            :show="showModal"
            :editing-item="editingItem"
            @close="closeModal"
        />
    </AppLayout>
</template>
