<template>
    <Head :title="`About ${settings.idol_name || 'Idol'}`" />

    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div class="relative min-h-screen flex flex-col selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-7xl px-6 lg:max-w-7xl mx-auto">
                <SiteHeader />

                <main class="mt-6 mb-12">
                    <!-- Section 1: Basic Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-8">
                        <div class="p-6 sm:p-8">
                            <div class="grid md:grid-cols-2 gap-8 items-start">
                                <!-- Photo -->
                                <div v-if="settings.idol_photo" class="flex justify-center">
                                    <img :src="settings.idol_photo" :alt="settings.idol_name" class="rounded-lg shadow-lg max-h-96 object-cover" />
                                </div>

                                <!-- Info -->
                                <div>
                                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ settings.idol_name }}</h3>

                                    <!-- Jikoshoukai -->
                                    <div v-if="settings.idol_jikoshoukai" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-gray-700 dark:text-gray-300 italic whitespace-pre-line">{{ settings.idol_jikoshoukai }}</p>
                                    </div>

                                    <!-- Biodata -->
                                    <div class="space-y-3 mb-6">
                                        <div v-if="settings.idol_birth_date || settings.idol_birth_place" class="flex items-start">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300 w-32">Kelahiran:</span>
                                            <span class="text-gray-600 dark:text-gray-400">
                                                <span v-if="settings.idol_birth_place">{{ settings.idol_birth_place }}</span>
                                                <span v-if="settings.idol_birth_place && settings.idol_birth_date">, </span>
                                                <span v-if="settings.idol_birth_date">{{ formatDate(settings.idol_birth_date) }}</span>
                                            </span>
                                        </div>
                                        <div v-if="settings.idol_blood_type" class="flex items-start">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300 w-32">Golongan Darah:</span>
                                            <span class="text-gray-600 dark:text-gray-400">{{ settings.idol_blood_type }}</span>
                                        </div>
                                        <div v-if="settings.idol_height" class="flex items-start">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300 w-32">Tinggi Badan:</span>
                                            <span class="text-gray-600 dark:text-gray-400">{{ settings.idol_height }} cm</span>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div v-if="settings.idol_description" class="prose dark:prose-invert max-w-none border-t pt-4">
                                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Description</h4>
                                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ settings.idol_description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Achievements & Discography -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-8">
                        <div class="p-6 sm:p-8">
                            <div class="grid md:grid-cols-2 gap-8">
                                <!-- Achievements -->
                                <div v-if="settings.idol_achievements">
                                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Achievements</h4>
                                    <div class="prose dark:prose-invert max-w-none">
                                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ settings.idol_achievements }}</p>
                                    </div>
                                </div>

                                <!-- Discography -->
                                <div v-if="settings.idol_discography">
                                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Discography</h4>
                                    <div class="prose dark:prose-invert max-w-none">
                                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ settings.idol_discography }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Social Media -->
                    <div v-if="settings.idol_social_media_instagram || settings.idol_social_media_tiktok || settings.idol_social_media_twitter" class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 sm:p-8">
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Follow on Social Media</h4>
                            <div class="flex flex-wrap justify-center gap-4">
                                <!-- Instagram Button -->
                                <a v-if="settings.idol_social_media_instagram"
                                   :href="settings.idol_social_media_instagram"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold rounded-lg shadow-lg hover:from-purple-600 hover:to-pink-600 transition-all transform hover:scale-105">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                    Follow on Instagram
                                </a>

                                <!-- TikTok Button -->
                                <a v-if="settings.idol_social_media_tiktok"
                                   :href="settings.idol_social_media_tiktok"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 px-6 py-3 bg-black text-white font-semibold rounded-lg shadow-lg hover:bg-gray-800 transition-all transform hover:scale-105">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                                    </svg>
                                    Follow on TikTok
                                </a>

                                <!-- Twitter Button -->
                                <a v-if="settings.idol_social_media_twitter"
                                   :href="settings.idol_social_media_twitter"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 px-6 py-3 bg-black text-white font-semibold rounded-lg shadow-lg hover:bg-gray-800 transition-all transform hover:scale-105">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                    </svg>
                                    Follow on Twitter/X
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

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
};
</script>
