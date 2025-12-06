<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { formatDate } from '@/Helpers/formatDate';
import { formatDateIndonesia } from '@/Helpers/formatDateIndonesia';
import SiteHeader from '@/Components/SiteHeader.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    latestPosts: {
        type: Array,
        default: () => [],
    },
    upcomingEvents: {
        type: Array,
        default: () => [],
    },
    liveStreamingStats: {
        type: Object,
        default: () => ({ showroom_count: 0, idn_app_count: 0 }),
    },
    latestGallery: {
        type: Array,
        default: () => [],
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList?.add('!hidden');
    document.getElementById('docs-card')?.classList?.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList?.add('!flex-row');
    document.getElementById('background')?.classList?.add('!hidden');
}

const page = usePage();
const heroSrc = computed(() => page.props.appSettings?.hero_image || '/storage/hero.jpg');
const showroomRoomId = computed(() => page.props.appSettings?.showroom_room_id || '416491');
const showroomLink = computed(() => page.props.appSettings?.showroom_link || 'https://www.showroom-live.com');
const teaterStats = computed(() => page.props.teaterStats || { total_shows: 0, unique_setlists: 0, unique_unit_songs: 0 });

// Animated counters
const animatedShows = ref(0);
const animatedSetlists = ref(0);
const animatedUnitSongs = ref(0);
const animatedShowroom = ref(0);
const animatedIdnApp = ref(0);

// Animate number counting
const animateCount = (start, end, duration, callback) => {
    const startTime = performance.now();
    const step = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easeOutQuad = progress * (2 - progress); // Easing function
        const current = Math.floor(start + (end - start) * easeOutQuad);
        callback(current);
        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };
    requestAnimationFrame(step);
};

// Showroom Live Status
const showroomStatus = ref({
    isLive: false,
    loading: true,
});

// YouTube Playlist ID extraction
const getYouTubePlaylistId = (url) => {
    if (!url) return null;
    const match = url.match(/[?&]list=([^&]+)/);
    return match ? match[1] : null;
};

const youtubePlaylistId = computed(() => {
    const url = page.props.appSettings?.youtube_playlist_url;
    return getYouTubePlaylistId(url);
});

const showYoutubePlaylist = computed(() => {
    return page.props.appSettings?.show_youtube_playlist === 'true' && youtubePlaylistId.value;
});

// Gallery Carousel
const showGalleryCarousel = computed(() => {
    return page.props.appSettings?.show_gallery_carousel === 'true' && page.props.latestGallery && page.props.latestGallery.length > 0;
});

const currentSlide = ref(0);
const autoplayInterval = ref(null);

const nextSlide = () => {
    if (page.props.latestGallery && page.props.latestGallery.length > 0) {
        currentSlide.value = (currentSlide.value + 1) % page.props.latestGallery.length;
    }
};

const prevSlide = () => {
    if (page.props.latestGallery && page.props.latestGallery.length > 0) {
        currentSlide.value = (currentSlide.value - 1 + page.props.latestGallery.length) % page.props.latestGallery.length;
    }
};

const goToSlide = (index) => {
    currentSlide.value = index;
};

const startAutoplay = () => {
    autoplayInterval.value = setInterval(nextSlide, 5000); // Change slide every 5 seconds
};

const stopAutoplay = () => {
    if (autoplayInterval.value) {
        clearInterval(autoplayInterval.value);
    }
};

// Fetch Showroom live status
const fetchShowroomStatus = async () => {
    try {
        const response = await fetch(`/api/showroom/live/${showroomRoomId.value}`);
        const data = await response.json();
        console.log('Showroom API response:', data); // Debug log
        showroomStatus.value = {
            isLive: data.live_status === 2,
            loading: false,
        };
    } catch (error) {
        console.error('Error fetching Showroom status:', error);
        showroomStatus.value.loading = false;
    }
};

onMounted(() => {
    fetchShowroomStatus();
    // Refresh status every 60 seconds
    setInterval(fetchShowroomStatus, 60000);

    // Animate statistics counters
    animateCount(0, teaterStats.value.total_shows, 3000, (val) => animatedShows.value = val);
    animateCount(0, teaterStats.value.unique_setlists, 3000, (val) => animatedSetlists.value = val);
    animateCount(0, teaterStats.value.unique_unit_songs, 3000, (val) => animatedUnitSongs.value = val);

    // Animate live streaming counters
    const liveStats = page.props.liveStreamingStats || { showroom_count: 0, idn_app_count: 0 };
    animateCount(0, liveStats.showroom_count, 3000, (val) => animatedShowroom.value = val);
    animateCount(0, liveStats.idn_app_count, 3000, (val) => animatedIdnApp.value = val);

    // Start carousel autoplay
    if (showGalleryCarousel.value) {
        startAutoplay();
    }
});
</script>

<template>

    <Head>
        <title>Home</title>
        <meta name="description" :content="page.props.appSettings?.desc_app || 'Welcome to our website'" />
        <meta property="og:title" :content="page.props.appSettings?.app_name || 'Fansight'" />
        <meta property="og:description" :content="page.props.appSettings?.desc_app || 'Welcome to our website'" />
        <meta property="og:image" :content="heroSrc" />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="page.props.appSettings?.app_name || 'Fansight'" />
        <meta name="twitter:description" :content="page.props.appSettings?.desc_app || 'Welcome to our website'" />
        <meta name="twitter:image" :content="heroSrc" />
    </Head>
    <div class="bg-gray-100 dark:bg-gray-900 text-black/50 dark:text-white/50 min-h-screen flex flex-col">
        <img id="background" class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg" />
        <div class="sticky top-0 z-50 left-0 right-0 w-full bg-gray-100 dark:bg-gray-900 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-6">
                <SiteHeader :can-login="canLogin" :can-register="canRegister" />
            </div>
        </div>

        <div class="flex-grow flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">

            <section class="mb-8 w-full relative z-0">
                <div class="relative w-full overflow-hidden">
                    <!-- Hero background image with responsive height. Mobile: fixed height, Medium+: 16:9 aspect ratio -->
                    <div class="w-full h-[500px] md:h-0 md:pb-[40%] relative">
                        <img :src="heroSrc" alt="Hero background"
                            class="absolute inset-0 w-full h-full object-cover object-top" />
                        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/40"></div>

                        <div class="absolute inset-0 flex items-center justify-start">
                            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6">
                                <div class="max-w-3xl text-left py-8 sm:py-12 text-white sm:pl-6 md:pl-12">
                                    <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold leading-tight">{{
                                        page.props.appSettings?.app_name }}</h1>
                                    <p class="mt-3 sm:mt-4 text-sm sm:text-base text-white/90 leading-relaxed">{{
                                        page.props.appSettings?.desc_app }}</p>

                                    <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row gap-3 justify-start">
                                        <component
                                            :is="page.props.appSettings?.hero_button_1_link?.startsWith('http') ? 'a' : Link"
                                            :href="page.props.appSettings?.hero_button_1_link || route('blog.index')"
                                            :target="page.props.appSettings?.hero_button_1_link?.startsWith('http') ? '_blank' : undefined"
                                            :rel="page.props.appSettings?.hero_button_1_link?.startsWith('http') ? 'noopener noreferrer' : undefined"
                                            class="inline-flex items-center justify-center px-5 py-3 bg-white text-black font-semibold rounded-md hover:opacity-90 transition">
                                            {{ page.props.appSettings?.hero_button_1_text || 'Info Lebih Lanjut' }}
                                        </component>
                                        <component
                                            :is="page.props.appSettings?.hero_button_2_link?.startsWith('http') ? 'a' : Link"
                                            :href="page.props.appSettings?.hero_button_2_link || route('blog.index')"
                                            :target="page.props.appSettings?.hero_button_2_link?.startsWith('http') ? '_blank' : undefined"
                                            :rel="page.props.appSettings?.hero_button_2_link?.startsWith('http') ? 'noopener noreferrer' : undefined"
                                            class="inline-flex items-center justify-center px-5 py-3 border border-white text-white font-semibold rounded-md hover:bg-white/10 transition">
                                            {{ page.props.appSettings?.hero_button_2_text || 'Temukan Kami' }}
                                        </component>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- About Idol Section (Optional) -->
            <section
                v-if="page.props.aboutSettings?.idol_show_on_welcome === 'true' || page.props.aboutSettings?.idol_show_on_welcome === true"
                class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:p-8">
                        <div class="grid md:grid-cols-2 gap-8 items-center">
                            <!-- Photo -->
                            <div v-if="page.props.aboutSettings.idol_photo"
                                class="flex justify-center order-2 md:order-1">
                                <img :src="page.props.aboutSettings.idol_photo"
                                    :alt="page.props.aboutSettings.idol_name"
                                    class="rounded-lg shadow-lg max-h-96 w-full object-cover" />
                            </div>

                            <!-- Info -->
                            <div class="order-1 md:order-2">
                                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{
                                    page.props.aboutSettings.idol_name }}</h3>
                                <div class="prose dark:prose-invert max-w-none">
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{
                                        page.props.aboutSettings.idol_description }}</p>
                                </div>

                                <!-- Social Media Icons -->
                                <div v-if="page.props.aboutSettings.idol_social_media_instagram || page.props.aboutSettings.idol_social_media_tiktok || page.props.aboutSettings.idol_social_media_twitter"
                                    class="mt-4 flex gap-3">
                                    <!-- Instagram -->
                                    <a v-if="page.props.aboutSettings.idol_social_media_instagram"
                                        :href="page.props.aboutSettings.idol_social_media_instagram" target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 via-pink-600 to-orange-500 hover:scale-110 transition-transform duration-200">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                        </svg>
                                    </a>

                                    <!-- TikTok -->
                                    <a v-if="page.props.aboutSettings.idol_social_media_tiktok"
                                        :href="page.props.aboutSettings.idol_social_media_tiktok" target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-black hover:scale-110 transition-transform duration-200">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z" />
                                        </svg>
                                    </a>

                                    <!-- Twitter/X -->
                                    <a v-if="page.props.aboutSettings.idol_social_media_twitter"
                                        :href="page.props.aboutSettings.idol_social_media_twitter" target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-black hover:scale-110 transition-transform duration-200">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                        </svg>
                                    </a>
                                </div>

                                <div class="mt-6">
                                    <Link
                                        :href="page.props.aboutSettings.idol_slug ? route('about.idol', { slug: page.props.aboutSettings.idol_slug }) : route('about.idol')"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 transition">
                                    Learn More
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div id="content" class="relative w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                <main class="mt-6">
                    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:gap-8">
                        <div class="flex flex-col gap-4">
                            <!-- Statistik Section -->
                            <div
                                class="flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 gap-4 p-6 sm:p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] lg:pb-5 dark:ring-zinc-800 dark:hover:text-white/70 w-full overflow-hidden">
                                <div class="w-full">
                                    <div class="grid grid-cols-3 gap-6 w-full">
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <div class="text-4xl sm:text-5xl lg:text-6xl font-bold text-[#FF2D20] mb-2">
                                                {{ animatedShows }}
                                            </div>
                                            <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Show
                                                Teater
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-center justify-center text-center">
                                            <div class="text-4xl sm:text-5xl lg:text-6xl font-bold text-[#FF2D20] mb-2">
                                                {{ animatedSetlists }}
                                            </div>
                                            <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Setlist
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-center justify-center text-center">
                                            <div class="text-4xl sm:text-5xl lg:text-6xl font-bold text-[#FF2D20] mb-2">
                                                {{ animatedUnitSongs }}
                                            </div>
                                            <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Unit Song
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="teaterStats.last_update" class="mt-4 text-center">
                                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                                            Last Update: <span class="font-medium">{{
                                                formatDateIndonesia(teaterStats.last_update) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 gap-4 p-6 sm:p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] lg:pb-10 dark:ring-zinc-800 dark:hover:text-white/70 w-full overflow-hidden">
                                <div class="grid grid-cols-2 gap-6 w-full">
                                    <component :is="showroomStatus.isLive ? 'a' : 'div'"
                                        :href="showroomStatus.isLive ? showroomLink : undefined"
                                        :target="showroomStatus.isLive ? '_blank' : undefined"
                                        :rel="showroomStatus.isLive ? 'noopener noreferrer' : undefined"
                                        class="flex flex-col items-center justify-center text-center"
                                        :class="{ 'cursor-pointer hover:opacity-80 transition': showroomStatus.isLive }"
                                        id="showroomlive">
                                        <div>
                                            <img src="https://static.showroom-live.com/assets/img/logo_guidelines/icon.png?t=1667879554"
                                                class="h-20 mb-4"></img>
                                        </div>
                                        <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400">{{
                                            animatedShowroom }}+ Live</div>
                                        <span v-if="!showroomStatus.loading"
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium inset-ring mt-2"
                                            :class="showroomStatus.isLive
                                                ? 'bg-green-400/10 text-green-400 inset-ring-green-500/20'
                                                : 'bg-red-400/10 text-red-400 inset-ring-red-400/20'">
                                            {{ showroomStatus.isLive ? 'Online' : 'Offline' }}
                                        </span>
                                    </component>

                                    <div id="idnlive" class="flex flex-col items-center justify-center text-center">
                                        <div>
                                            <img src="https://play-lh.googleusercontent.com/vHCUNv03NNuh_aNKRCP63wUpC-HHhPXyrL_gJdFr_Xn7lgsamEAoFhG7mtz1gVBjYA=w480-h960-rw"
                                                class="h-20 mb-4"></img>
                                        </div>
                                        <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400">{{
                                            animatedIdnApp }}+ Live
                                        </div>
                                        <span
                                            class="inline-flex items-center rounded-md bg-red-400/10 px-2 py-1 text-xs font-medium text-red-400 inset-ring inset-ring-red-400/20 mt-2">Not
                                            Connected</span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-2 rounded-lg p-4 sm:px-6 sm:py-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] lg:pb-10 bg-white dark:bg-gray-800 dark:ring-zinc-800 w-full overflow-hidden">

                                <div class="pt-3 sm:pt-5 flex-1 min-w-0">
                                    <h2 class="text-lg sm:text-xl font-semibold text-black dark:text-white break-words">
                                        Oshimen Calendar</h2>

                                    <div v-if="!upcomingEvents || upcomingEvents.length === 0"
                                        class="flex-1 min-w-0 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700 p-3 mt-2">
                                        <div class="text-sm text-gray-600 dark:text-gray-300 text-center">
                                            No upcoming events in the next 7 days
                                        </div>
                                    </div>

                                    <div v-for="(event, index) in upcomingEvents" :key="index"
                                        class="flex-1 min-w-0 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700 p-3 mt-2">
                                        <div class="flex items-start justify-between gap-2 flex-wrap sm:flex-nowrap">
                                            <span :class="[
                                                'gap-x-1 py-1 px-2 rounded-full text-xs font-medium text-white',
                                                event.color
                                            ]">{{ event.type }}</span>
                                            <div
                                                class="text-sm sm:text-md font-medium text-black dark:text-white truncate flex-1 min-w-0">
                                                {{ event.name }}</div>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap shrink-0">
                                                {{ formatDateIndonesia(event.date) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Artikel Terbaru -->
                        <div id="docs-list"
                            class="flex flex-col gap-3 lg:pb-10 p-4 sm:p-6 rounded-lg shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 bg-white dark:bg-gray-800 w-full overflow-hidden">
                            <h3 class="text-lg font-semibold text-black dark:text-white">Latest Posts</h3>
                            <p class="text-sm/relaxed break-words">
                                Dapatkan kabar terbaru tentang idolamu di sini.
                            </p>
                            <ul class="space-y-3">
                                <li v-if="!latestPosts || latestPosts.length === 0"
                                    class="rounded-lg bg-white p-4 dark:bg-zinc-900">
                                    <div class="text-sm text-gray-600 dark:text-gray-300">No posts yet.
                                        <Link :href="route('blog.index')" class="underline">View blog</Link>
                                    </div>
                                </li>
                                <li v-for="post in (latestPosts || []).slice(0, 5)" :key="post.id"
                                    class="block rounded-lg bg-white dark:bg-gray-800 p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.04)] ring-1 ring-white/[0.03] transition hover:shadow-md border border-gray-200 hover:border-gray-500 dark:hover:border-zinc-700 w-full overflow-hidden">
                                    <Link :href="route('blog.show', post.slug)" class="flex items-start gap-3 w-full">
                                    <template v-if="post.thumbnail">
                                        <!-- Show a smaller thumbnail on mobile and a slightly
                                             larger one on sm+ screens. -->
                                        <img :src="post.thumbnail" alt="thumbnail"
                                            class="w-16 h-12 sm:w-24 sm:h-16 rounded-md object-cover shrink-0" />
                                    </template>

                                    <div class="flex-1 min-w-0 overflow-hidden">
                                        <div class="flex items-start justify-between gap-2 flex-wrap sm:flex-nowrap">
                                            <div
                                                class="text-sm sm:text-md font-medium text-black dark:text-white truncate flex-1 min-w-0">
                                                {{
                                                    post.title }}</div>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap shrink-0">
                                                {{ post.published_at ?
                                                    formatDate(post.published_at) : '-' }}</div>
                                        </div>
                                        <div
                                            class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2 break-words">
                                            {{
                                                post.excerpt ??
                                                post.summary ?? (post.body && post.body.substring ? post.body.substring(0,
                                                    120) + '...' :
                                                    '') }}</div>
                                    </div>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <!-- Gallery Carousel Section (Optional) -->
        <section v-if="showGalleryCarousel" class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                        Latest Gallery
                    </h2>
                    <Link href="/gallery"
                        class="text-[#FF2D20] hover:text-[#FF5643] font-medium flex items-center gap-2">
                        View All
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>

                <div class="relative">
                    <!-- Carousel Container -->
                    <div class="overflow-hidden rounded-lg">
                        <div class="relative aspect-video bg-gray-100 dark:bg-gray-900">
                            <!-- Slides -->
                            <div v-for="(photo, index) in page.props.latestGallery" :key="photo.id"
                                v-show="currentSlide === index"
                                class="absolute inset-0 transition-opacity duration-500"
                                :class="currentSlide === index ? 'opacity-100' : 'opacity-0'">
                                <img :src="photo.image_path" :alt="photo.title"
                                    class="w-full h-full object-contain">
                                <div
                                    class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4 sm:p-6">
                                    <h3 class="text-white font-bold text-lg sm:text-xl mb-1">{{ photo.title }}</h3>
                                    <p v-if="photo.description" class="text-gray-200 text-sm">{{ photo.description }}
                                    </p>
                                    <p v-if="photo.credit" class="text-gray-300 text-xs mt-1">Credit: {{ photo.credit
                                        }}</p>
                                </div>
                            </div>

                            <!-- Previous Button -->
                            <button @click="prevSlide"
                                class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 sm:p-3 rounded-full transition-all">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <!-- Next Button -->
                            <button @click="nextSlide"
                                class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 sm:p-3 rounded-full transition-all">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Dots Navigation -->
                    <div class="flex justify-center gap-2 mt-4">
                        <button v-for="(photo, index) in page.props.latestGallery" :key="`dot-${photo.id}`"
                            @click="goToSlide(index)"
                            class="w-2 h-2 sm:w-3 sm:h-3 rounded-full transition-all"
                            :class="currentSlide === index ? 'bg-[#FF2D20] w-6 sm:w-8' : 'bg-gray-300 dark:bg-gray-600 hover:bg-gray-400'">
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- YouTube Playlist Section (Optional) -->
        <section v-if="showYoutubePlaylist" class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Video Playlist</h3>
                    <div class="aspect-video w-full">
                        <iframe :src="`https://www.youtube.com/embed/videoseries?list=${youtubePlaylistId}`"
                            class="w-full h-full rounded-lg" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </section>

        <div class="w-full">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <Footer type="public" />
            </div>
        </div>
    </div>
</template>
