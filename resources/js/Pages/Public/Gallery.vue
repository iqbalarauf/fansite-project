<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    galleries: Array,
});

// Extract YouTube video ID from URL
const getYouTubeVideoId = (url) => {
    if (!url) return null;
    const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
    return match ? match[1] : null;
};

// Lightbox state
const lightboxOpen = ref(false);
const lightboxItem = ref(null);

const openLightbox = (item) => {
    lightboxItem.value = item;
    lightboxOpen.value = true;
};

const closeLightbox = () => {
    lightboxOpen.value = false;
    lightboxItem.value = null;
};

// Filter by type
const selectedFilter = ref('all');
const filteredGalleries = computed(() => {
    if (selectedFilter.value === 'all') return props.galleries || [];
    return (props.galleries || []).filter(item => item.type === selectedFilter.value);
});
</script>

<template>
    <Head>
        <title>Gallery</title>
    </Head>

    <div class="bg-gray-100 dark:bg-gray-900 text-black/50 dark:text-white/50 min-h-screen flex flex-col">
        <div class="sticky top-0 z-50 left-0 right-0 w-full bg-gray-100 dark:bg-gray-900 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-6">
                <SiteHeader />
            </div>
        </div>

        <div class="flex-grow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">Gallery</h1>

                    <!-- Filter Tabs -->
                    <div class="flex gap-2 flex-wrap">
                        <button
                            @click="selectedFilter = 'all'"
                            :class="[
                                'px-4 py-2 rounded-md text-sm font-medium transition',
                                selectedFilter === 'all'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]">
                            All
                        </button>
                        <button
                            @click="selectedFilter = 'photo'"
                            :class="[
                                'px-4 py-2 rounded-md text-sm font-medium transition',
                                selectedFilter === 'photo'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]">
                            Photos
                        </button>
                        <button
                            @click="selectedFilter = 'video'"
                            :class="[
                                'px-4 py-2 rounded-md text-sm font-medium transition',
                                selectedFilter === 'video'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]">
                            Videos
                        </button>
                    </div>
                </div>

                <!-- Gallery Grid -->
                <div v-if="filteredGalleries.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="item in filteredGalleries"
                        :key="item.id"
                        @click="openLightbox(item)"
                        class="group cursor-pointer bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-md hover:shadow-xl transition-shadow">

                        <!-- Image/Video Preview -->
                        <div class="aspect-square bg-gray-200 dark:bg-gray-700 relative overflow-hidden">
                            <!-- Photo Type -->
                            <img
                                v-if="item.type === 'photo' && item.image_path"
                                :src="item.image_path"
                                :alt="item.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                            <!-- Video Type -->
                            <div v-else-if="item.type === 'video' && item.video_url" class="relative w-full h-full">
                                <img
                                    :src="`https://img.youtube.com/vi/${getYouTubeVideoId(item.video_url)}/maxresdefault.jpg`"
                                    :alt="item.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 group-hover:bg-opacity-50 transition-all">
                                    <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1 line-clamp-2">{{ item.title }}</h3>
                            <p v-if="item.credit" class="text-xs text-gray-500 dark:text-gray-400 italic">{{ item.credit }}</p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No items found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try selecting a different filter.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="w-full">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <Footer type="public" />
            </div>
        </div>

        <!-- Lightbox Modal -->
        <div
            v-if="lightboxOpen && lightboxItem"
            @click="closeLightbox"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 p-4">
            <div @click.stop class="relative max-w-6xl w-full">
                <!-- Close Button -->
                <button
                    @click="closeLightbox"
                    class="absolute -top-12 right-0 text-white hover:text-gray-300 transition">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Content -->
                <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                    <!-- Photo -->
                    <img
                        v-if="lightboxItem.type === 'photo' && lightboxItem.image_path"
                        :src="lightboxItem.image_path"
                        :alt="lightboxItem.title"
                        class="w-full max-h-[70vh] object-contain bg-gray-100 dark:bg-gray-900">

                    <!-- Video -->
                    <div v-else-if="lightboxItem.type === 'video' && lightboxItem.video_url" class="aspect-video">
                        <iframe
                            :src="`https://www.youtube.com/embed/${getYouTubeVideoId(lightboxItem.video_url)}`"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>

                    <!-- Info -->
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ lightboxItem.title }}</h2>
                        <p v-if="lightboxItem.description" class="text-gray-600 dark:text-gray-300 mb-2">{{ lightboxItem.description }}</p>
                        <p v-if="lightboxItem.credit" class="text-sm text-gray-500 dark:text-gray-400 italic">Photo/Video by: {{ lightboxItem.credit }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
