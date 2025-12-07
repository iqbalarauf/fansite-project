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

                    <!-- Section 3: Social Media Embeds -->
                    <div v-if="settings.idol_social_media_instagram || settings.idol_social_media_tiktok || settings.idol_social_media_twitter" class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 sm:p-8">
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Follow on Social Media</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 justify-center items-start">
                                <!-- Instagram Embed -->
                                <div v-if="instagramUsername" class="flex flex-col items-center w-full max-w-[400px]">
                                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b pb-2">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                        </svg>
                                        Instagram
                                    </h5>
                                    <iframe
                                        :src="`https://www.instagram.com/${instagramUsername}/embed`"
                                        class="w-full h-[400px] border-0 rounded-lg bg-white dark:bg-gray-900"
                                        scrolling="no"
                                        frameborder="0"
                                        allowtransparency="true"
                                        loading="lazy"></iframe>
                                    <a :href="settings.idol_social_media_instagram"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="mt-3 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        View on Instagram →
                                    </a>
                                </div>

                                <!-- TikTok Embed -->
                                <div v-if="tiktokUsername" class="flex flex-col items-center w-full max-w-[325px]">
                                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b pb-2">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                                        </svg>
                                        TikTok
                                    </h5>
                                    <blockquote
                                        class="tiktok-embed w-full"
                                        cite="https://www.tiktok.com/@{{ tiktokUsername }}"
                                        :data-unique-id="tiktokUsername"
                                        data-embed-type="creator">
                                        <section>
                                            <a :href="`https://www.tiktok.com/@${tiktokUsername}`"
                                               target="_blank"
                                               rel="noopener noreferrer">
                                                @{{ tiktokUsername }}
                                            </a>
                                        </section>
                                    </blockquote>
                                </div>

                                <!-- Twitter/X Card -->
                                <div v-if="twitterUsername" class="flex flex-col items-center w-full max-w-[325px]">
                                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b pb-2">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                        </svg>
                                        X (Twitter)
                                    </h5>
                                    <div class="w-full bg-white dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                        <!-- Profile Header -->
                                        <div class="p-6 bg-gradient-to-r from-blue-400 to-blue-600 h-24"></div>
                                        <div class="px-6 pb-6">
                                            <!-- Avatar -->
                                            <div class="-mt-12 mb-4">
                                                <div v-if="twitterProfilePicture" class="w-24 h-24 rounded-full border-4 border-white dark:border-gray-900 overflow-hidden bg-white">
                                                    <img
                                                        :src="twitterProfilePicture"
                                                        :alt="`${settings.idol_name} Twitter Profile`"
                                                        class="w-full h-full object-cover"
                                                        @error="handleTwitterImageError" />
                                                </div>
                                                <div v-else-if="settings.idol_photo" class="w-24 h-24 rounded-full border-4 border-white dark:border-gray-900 overflow-hidden">
                                                    <img :src="settings.idol_photo" :alt="settings.idol_name" class="w-full h-full object-cover" />
                                                </div>
                                                <div v-else class="w-24 h-24 rounded-full bg-gray-300 dark:bg-gray-700 border-4 border-white dark:border-gray-900 flex items-center justify-center">
                                                    <svg class="w-12 h-12 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <!-- Username -->
                                            <h6 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                                                {{ settings.idol_name || 'Profile' }}
                                            </h6>
                                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                                @{{ twitterUsername }}
                                            </p>
                                            <!-- Follow Button -->
                                            <a
                                                :href="`https://twitter.com/${twitterUsername}`"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="block w-full text-center px-4 py-2 bg-black hover:bg-gray-800 text-white font-semibold rounded-full transition-colors">
                                                Follow on X
                                            </a>
                                            <!-- Alternative: View Posts -->
                                            <a
                                                :href="`https://twitter.com/${twitterUsername}`"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="block w-full text-center mt-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                                                View Posts →
                                            </a>
                                        </div>
                                    </div>
                                </div>
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
import { computed, onMounted } from 'vue';

const props = defineProps({
    settings: Object
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
};

// Extract username from social media URLs
const extractUsername = (url, platform) => {
    if (!url) return null;

    try {
        // Remove trailing slash
        url = url.replace(/\/$/, '');

        switch(platform) {
            case 'instagram':
                // https://instagram.com/username or https://www.instagram.com/username
                const igMatch = url.match(/instagram\.com\/([^\/\?]+)/);
                return igMatch ? igMatch[1] : null;

            case 'tiktok':
                // https://tiktok.com/@username or https://www.tiktok.com/@username
                const ttMatch = url.match(/tiktok\.com\/@?([^\/\?]+)/);
                return ttMatch ? ttMatch[1].replace('@', '') : null;

            case 'twitter':
                // https://twitter.com/username or https://x.com/username
                const twMatch = url.match(/(?:twitter|x)\.com\/([^\/\?]+)/);
                return twMatch ? twMatch[1] : null;

            default:
                return null;
        }
    } catch (e) {
        console.error(`Error extracting ${platform} username:`, e);
        return null;
    }
};

const instagramUsername = computed(() =>
    extractUsername(props.settings.idol_social_media_instagram, 'instagram')
);

const tiktokUsername = computed(() =>
    extractUsername(props.settings.idol_social_media_tiktok, 'tiktok')
);

const twitterUsername = computed(() =>
    extractUsername(props.settings.idol_social_media_twitter, 'twitter')
);

// Get Twitter profile picture URL
const twitterProfilePicture = computed(() => {
    if (!twitterUsername.value) return null;
    // Using Twitter's profile picture endpoint (redirects to actual image)
    // Size options: normal (48x48), bigger (73x73), mini (24x24), original (original size)
    return `https://unavatar.io/twitter/${twitterUsername.value}`;
});

// Handle Twitter image loading error
const handleTwitterImageError = (event) => {
    // Fallback to idol photo if Twitter image fails to load
    if (props.settings.idol_photo) {
        event.target.src = props.settings.idol_photo;
    }
};

// Load external embed scripts
onMounted(() => {
    // Load TikTok embed script
    if (tiktokUsername.value) {
        const tiktokScript = document.createElement('script');
        tiktokScript.src = 'https://www.tiktok.com/embed.js';
        tiktokScript.async = true;
        document.body.appendChild(tiktokScript);
    }
});
</script>
