<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { formatDate } from '@/Helpers/formatDate';
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
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList?.add('!hidden');
    document.getElementById('docs-card')?.classList?.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList?.add('!flex-row');
    document.getElementById('background')?.classList?.add('!hidden');
}

const page = usePage();
const heroSrc = computed(() => page.props.appSettings?.hero_image || '/storage/hero.jpg');
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
                    <!-- Hero background image with responsive height -->
                    <!-- Mobile: fixed height, Medium+: 16:9 aspect ratio -->
                    <div class="w-full h-[500px] md:h-0 md:pb-[56.25%] relative">
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
                                        <Link :href="route('blog.index')"
                                            class="inline-flex items-center justify-center px-5 py-3 bg-white text-black font-semibold rounded-md hover:opacity-90 transition">
                                        Info Lebih Lanjut</Link>
                                        <Link :href="route('blog.index')"
                                            class="inline-flex items-center justify-center px-5 py-3 border border-white text-white font-semibold rounded-md hover:bg-white/10 transition">
                                        Temukan Kami</Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="relative w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                <main class="mt-6">
                    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:gap-8">
                        <div class="flex flex-col gap-4">
                            <!-- Statistik Section -->
                            <div
                                class="flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 gap-4 p-6 sm:p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20] w-full overflow-hidden">
                                <div class="grid grid-cols-3 gap-6 w-full">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="text-4xl sm:text-5xl lg:text-6xl font-bold text-[#FF2D20] mb-2">100+
                                        </div>
                                        <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Show
                                            Teater
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="text-4xl sm:text-5xl lg:text-6xl font-bold text-[#FF2D20] mb-2">6
                                        </div>
                                        <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Setlist
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="text-4xl sm:text-5xl lg:text-6xl font-bold text-[#FF2D20] mb-2">10
                                        </div>
                                        <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Unit Song
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 gap-4 p-6 sm:p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20] w-full overflow-hidden">
                                <div class="grid grid-cols-2 gap-6 w-full">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div>
                                            <img src="https://static.showroom-live.com/assets/img/logo_guidelines/icon.png?t=1667879554"
                                                class="h-20 mb-4"></img>
                                        </div>
                                        <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400">80+ Live
                                        </div>
                                        <span
                                            class="inline-flex items-center rounded-md bg-green-400/10 px-2 py-1 text-xs font-medium text-green-400 inset-ring inset-ring-green-500/20 mt-2">Online</span>
                                    </div>

                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div>
                                            <img src="https://play-lh.googleusercontent.com/vHCUNv03NNuh_aNKRCP63wUpC-HHhPXyrL_gJdFr_Xn7lgsamEAoFhG7mtz1gVBjYA=w480-h960-rw"
                                                class="h-20 mb-4"></img>
                                        </div>
                                        <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400">200+ Live
                                        </div>
                                        <span
                                            class="inline-flex items-center rounded-md bg-red-400/10 px-2 py-1 text-xs font-medium text-red-400 inset-ring inset-ring-red-400/20 mt-2">Offline</span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-2 rounded-lg p-4 sm:px-6 sm:py-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] lg:pb-10 bg-white dark:bg-gray-800 dark:ring-zinc-800 w-full overflow-hidden">

                                <div class="pt-3 sm:pt-5 flex-1 min-w-0">
                                    <h2 class="text-lg sm:text-xl font-semibold text-black dark:text-white break-words">
                                        Oshimen Calendar</h2>

                                    <div class="flex-1 min-w-0 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700 p-3 mt-2">
                                        <div class="flex items-start justify-between gap-2 flex-wrap sm:flex-nowrap">
                                            <span class="gap-x-1 py-1 px-2 rounded-full text-xs font-medium bg-red-500 text-white">SHOW</span>
                                            <div
                                                class="text-sm sm:text-md font-medium text-black dark:text-white truncate flex-1 min-w-0">
                                                Pertaruhan Cinta</div>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap shrink-0">
                                                28 November 2025</div>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700 p-3 mt-2">
                                        <div class="flex items-start justify-between gap-2 flex-wrap sm:flex-nowrap">
                                            <span class="gap-x-1 py-1 px-2 rounded-full text-xs font-medium bg-green-500 text-white">OFF-AIR</span>
                                            <div
                                                class="text-sm sm:text-md font-medium text-black dark:text-white truncate flex-1 min-w-0">
                                                Don't Play Play Festival - Jakarta</div>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap shrink-0">
                                                29 November 2025</div>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700 p-3 mt-2">
                                        <div class="flex items-start justify-between gap-2 flex-wrap sm:flex-nowrap">
                                            <span class="gap-x-1 py-1 px-2 rounded-full text-xs font-medium bg-green-500 text-white">OFF-AIR</span>
                                            <div
                                                class="text-sm sm:text-md font-medium text-black dark:text-white truncate flex-1 min-w-0">
                                                Now Playing Festival - Bandung</div>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap shrink-0">
                                                30 November 2025</div>
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

        <div class="w-full">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <Footer type="public" />
            </div>
        </div>
    </div>
</template>
