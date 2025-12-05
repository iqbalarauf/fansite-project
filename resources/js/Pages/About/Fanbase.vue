<template>
    <Head :title="`About ${settings.fanbase_name || 'Fanbase'}`" />

    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-7xl px-6 lg:max-w-7xl">
                <SiteHeader />

                <main class="mt-6 mb-12">
                    <!-- Section 1: Basic Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-8">
                        <div class="p-6 sm:p-8">
                            <div class="grid md:grid-cols-2 gap-8 items-center">
                                <!-- Logo -->
                                <div v-if="settings.fanbase_logo" class="flex justify-center">
                                    <img :src="settings.fanbase_logo" :alt="settings.fanbase_name" class="rounded-lg shadow-lg max-h-96 object-contain" />
                                </div>

                                <!-- Info -->
                                <div>
                                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ settings.fanbase_name }}</h3>
                                    <div class="prose dark:prose-invert max-w-none">
                                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ settings.fanbase_description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Activities & Gallery -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-8">
                        <div class="p-6 sm:p-8">
                            <!-- Activities -->
                            <div v-if="settings.fanbase_activities" class="mb-8">
                                <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Our Activities</h4>
                                <div class="prose dark:prose-invert max-w-none">
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ settings.fanbase_activities }}</p>
                                </div>
                            </div>

                            <!-- Gallery -->
                            <div v-if="settings.fanbase_gallery && settings.fanbase_gallery.length > 0">
                                <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Gallery</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="(image, index) in settings.fanbase_gallery" :key="index" class="relative group">
                                        <img :src="image" :alt="'Gallery image ' + (index + 1)" class="w-full h-64 object-cover rounded-lg shadow-md transition-transform group-hover:scale-105" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: CTA (Optional) -->
                    <div v-if="settings.fanbase_cta_enabled && settings.fanbase_cta_enabled !== '0'"
                         class="relative overflow-hidden shadow-xl sm:rounded-lg mb-8"
                         :style="{ backgroundImage: settings.fanbase_cta_background ? `url(${settings.fanbase_cta_background})` : 'none' }">
                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-black opacity-50"></div>

                        <!-- Content -->
                        <div class="relative p-12 sm:p-16 text-center">
                            <h4 class="text-3xl font-bold text-white mb-4">{{ settings.fanbase_cta_title }}</h4>
                            <p class="text-xl text-white mb-8">{{ settings.fanbase_cta_description }}</p>

                            <div class="flex flex-wrap justify-center gap-4">
                                <a v-if="settings.fanbase_cta_button1_text && settings.fanbase_cta_button1_link"
                                   :href="settings.fanbase_cta_button1_link"
                                   class="inline-flex items-center px-6 py-3 bg-white border border-transparent rounded-md font-semibold text-indigo-600 uppercase tracking-widest hover:bg-gray-100 active:bg-gray-200 transition">
                                    {{ settings.fanbase_cta_button1_text }}
                                </a>

                                <a v-if="settings.fanbase_cta_button2_text && settings.fanbase_cta_button2_link"
                                   :href="settings.fanbase_cta_button2_link"
                                   class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                                    {{ settings.fanbase_cta_button2_text }}
                                </a>
                            </div>
                        </div>
                    </div>
                </main>

                <Footer />
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    settings: Object
});
</script><style scoped>
[style*="background-image"] {
    background-size: cover;
    background-position: center;
}
</style>
