<template>
    <Head :title="`About ${settings.idol_name || 'Idol'}`" />

    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-7xl px-6 lg:max-w-7xl">
                <SiteHeader />

                <main class="mt-6 mb-12">
                    <!-- Section 1: Basic Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-8">
                        <div class="p-6 sm:p-8">
                            <div class="grid md:grid-cols-2 gap-8 items-center">
                                <!-- Photo -->
                                <div v-if="settings.idol_photo" class="flex justify-center">
                                    <img :src="settings.idol_photo" :alt="settings.idol_name" class="rounded-lg shadow-lg max-h-96 object-cover" />
                                </div>

                                <!-- Info -->
                                <div>
                                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ settings.idol_name }}</h3>
                                    <div class="prose dark:prose-invert max-w-none">
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
                    <div v-if="settings.idol_social_media" class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 sm:p-8">
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Follow on Social Media</h4>
                            <div class="social-embeds" v-html="settings.idol_social_media"></div>
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
.social-embeds {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    justify-items: center;
}

.social-embeds :deep(iframe),
.social-embeds :deep(blockquote) {
    max-width: 100%;
}
</style>
