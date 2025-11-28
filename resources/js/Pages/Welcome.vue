<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { formatDate } from '@/Helpers/formatDate';
import SiteHeader from '@/Components/SiteHeader.vue';

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
const currentYear = new Date().getFullYear();
const heroSrc = computed(() => page.props.appSettings?.hero_image || '/storage/hero.jpg');
</script>

<template>

    <Head title="Welcome" />
    <div class="bg-gray-100 dark:bg-gray-900 text-black/50 dark:text-white/50">
        <img id="background" class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg" />
        <div
            class="min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="sticky top-0 z-50 left-0 right-0 w-full bg-gray-100 dark:bg-gray-900 backdrop-blur-sm">
                <div class="max-w-7xl mx-auto px-6">
                    <SiteHeader :can-login="canLogin" :can-register="canRegister" />
                </div>
            </div>

              <section class="mb-8 w-full relative z-0">
                <div class="relative w-full overflow-hidden">
                    <!-- Hero background image with responsive height -->
                    <img :src="heroSrc" alt="Hero background" class="w-full h-[500px] sm:h-[400px] md:h-[450px] lg:h-[500px] object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/40"></div>

                    <div class="absolute inset-0 flex items-center justify-start">
                        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6">
                            <div class="max-w-3xl text-left py-8 sm:py-12 text-white sm:pl-6 md:pl-12">
                                <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold leading-tight">{{ page.props.appSettings?.app_name }}</h1>
                                <p class="mt-3 sm:mt-4 text-sm sm:text-base text-white/90 leading-relaxed">{{ page.props.appSettings?.desc_app }}</p>

                                <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row gap-3 justify-start">
                                    <Link :href="route('blog.index')" class="inline-flex items-center justify-center px-5 py-3 bg-white text-black font-semibold rounded-md hover:opacity-90 transition">Info Lebih Lanjut</Link>
                                    <Link :href="route('blog.index')" class="inline-flex items-center justify-center px-5 py-3 border border-white text-white font-semibold rounded-md hover:bg-white/10 transition">Temukan Kami</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

              <div class="relative w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                <main class="mt-6">
                    <!-- Single column on small screens, two columns from md upwards -->
                    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:gap-8">
                        <!-- Artikel Terbaru - 5 Artikel -->
                        <div id="docs-list" class="flex flex-col gap-3 md:row-span-3 lg:pb-10 p-4 sm:p-6 rounded-lg shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 bg-white dark:bg-gray-800 w-full overflow-hidden">
                            <h3 class="text-lg font-semibold text-black dark:text-white">Latest Posts</h3>
                            <p class="text-sm/relaxed break-words">
                                    Dapatkan kabar terbaru tentang idolamu di sini. Kamu juga bisa berkontribusi untuk menulis dengan login dan tulis post baru di "Create Post"
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
                                            <div class="text-sm sm:text-md font-medium text-black dark:text-white truncate flex-1 min-w-0">{{
                                                post.title }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap shrink-0">{{ post.published_at ? formatDate(post.published_at) : '-' }}</div>
                                        </div>
                                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2 break-words">{{
                                            post.excerpt ??
                                            post.summary ?? (post.body && post.body.substring ? post.body.substring(0,
                                            120) + '...' :
                                            '') }}</div>
                                    </div>
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <a href="https://laracasts.com"
                            class="flex items-start gap-4 rounded-lg bg-white dark:bg-gray-800 p-4 sm:p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20] w-full overflow-hidden">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                <svg class="size-5 sm:size-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <g fill="#FF2D20">
                                        <path
                                            d="M24 8.25a.5.5 0 0 0-.5-.5H.5a.5.5 0 0 0-.5.5v12a2.5 2.5 0 0 0 2.5 2.5h19a2.5 2.5 0 0 0 2.5-2.5v-12Zm-7.765 5.868a1.221 1.221 0 0 1 0 2.264l-6.626 2.776A1.153 1.153 0 0 1 8 18.123v-5.746a1.151 1.151 0 0 1 1.609-1.035l6.626 2.776ZM19.564 1.677a.25.25 0 0 0-.177-.427H15.6a.106.106 0 0 0-.072.03l-4.54 4.543a.25.25 0 0 0 .177.427h3.783c.027 0 .054-.01.073-.03l4.543-4.543ZM22.071 1.318a.047.047 0 0 0-.045.013l-4.492 4.492a.249.249 0 0 0 .038.385.25.25 0 0 0 .14.042h5.784a.5.5 0 0 0 .5-.5v-2a2.5 2.5 0 0 0-1.925-2.432ZM13.014 1.677a.25.25 0 0 0-.178-.427H9.101a.106.106 0 0 0-.073.03l-4.54 4.543a.25.25 0 0 0 .177.427H8.4a.106.106 0 0 0 .073-.03l4.54-4.543ZM6.513 1.677a.25.25 0 0 0-.177-.427H2.5A2.5 2.5 0 0 0 0 3.75v2a.5.5 0 0 0 .5.5h1.4a.106.106 0 0 0 .073-.03l4.54-4.543Z" />
                                    </g>
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5 flex-1 min-w-0">
                                <h2 class="text-lg sm:text-xl font-semibold text-black dark:text-white break-words">Laracasts</h2>

                                <p class="mt-3 sm:mt-4 text-sm/relaxed break-words">
                                    Laracasts offers thousands of video tutorials on Laravel, PHP, and JavaScript
                                    development. Check
                                    them out, see for yourself, and massively level up your development skills in the
                                    process.
                                </p>
                            </div>

                            <svg class="size-6 shrink-0 self-center stroke-[#FF2D20]" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                            </svg>
                        </a>

                        <a href="https://laravel-news.com"
                            class="flex items-start gap-4 rounded-lg bg-white dark:bg-gray-800 p-4 sm:p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20] w-full overflow-hidden">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                <svg class="size-5 sm:size-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <g fill="#FF2D20">
                                        <path
                                            d="M8.75 4.5H5.5c-.69 0-1.25.56-1.25 1.25v4.75c0 .69.56 1.25 1.25 1.25h3.25c.69 0 1.25-.56 1.25-1.25V5.75c0-.69-.56-1.25-1.25-1.25Z" />
                                        <path
                                            d="M24 10a3 3 0 0 0-3-3h-2V2.5a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2V20a3.5 3.5 0 0 0 3.5 3.5h17A3.5 3.5 0 0 0 24 20V10ZM3.5 21.5A1.5 1.5 0 0 1 2 20V3a.5.5 0 0 1 .5-.5h14a.5.5 0 0 1 .5.5v17c0 .295.037.588.11.874a.5.5 0 0 1-.484.625L3.5 21.5ZM22 20a1.5 1.5 0 1 1-3 0V9.5a.5.5 0 0 1 .5-.5H21a1 1 0 0 1 1 1v10Z" />
                                        <path
                                            d="M12.751 6.047h2a.75.75 0 0 1 .75.75v.5a.75.75 0 0 1-.75.75h-2A.75.75 0 0 1 12 7.3v-.5a.75.75 0 0 1 .751-.753ZM12.751 10.047h2a.75.75 0 0 1 .75.75v.5a.75.75 0 0 1-.75.75h-2A.75.75 0 0 1 12 11.3v-.5a.75.75 0 0 1 .751-.753ZM4.751 14.047h10a.75.75 0 0 1 .75.75v.5a.75.75 0 0 1-.75.75h-10A.75.75 0 0 1 4 15.3v-.5a.75.75 0 0 1 .751-.753ZM4.75 18.047h7.5a.75.75 0 0 1 .75.75v.5a.75.75 0 0 1-.75.75h-7.5A.75.75 0 0 1 4 19.3v-.5a.75.75 0 0 1 .75-.753Z" />
                                    </g>
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5 flex-1 min-w-0">
                                <h2 class="text-lg sm:text-xl font-semibold text-black dark:text-white break-words">Laravel News</h2>

                                <p class="mt-3 sm:mt-4 text-sm/relaxed break-words">
                                    Laravel News is a community driven portal and newsletter aggregating all of the
                                    latest and most
                                    important news in the Laravel ecosystem, including new package releases and
                                    tutorials.
                                </p>
                            </div>

                            <svg class="size-6 shrink-0 self-center stroke-[#FF2D20]" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                            </svg>
                        </a>

                        <div
                            class="flex items-start gap-4 rounded-lg p-4 sm:p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] lg:pb-10 bg-white dark:bg-gray-800 dark:ring-zinc-800 w-full overflow-hidden">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                <svg class="size-5 sm:size-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <g fill="#FF2D20">
                                        <path
                                            d="M16.597 12.635a.247.247 0 0 0-.08-.237 2.234 2.234 0 0 1-.769-1.68c.001-.195.03-.39.084-.578a.25.25 0 0 0-.09-.267 8.8 8.8 0 0 0-4.826-1.66.25.25 0 0 0-.268.181 2.5 2.5 0 0 1-2.4 1.824.045.045 0 0 0-.045.037 12.255 12.255 0 0 0-.093 3.86.251.251 0 0 0 .208.214c2.22.366 4.367 1.08 6.362 2.118a.252.252 0 0 0 .32-.079 10.09 10.09 0 0 0 1.597-3.733ZM13.616 17.968a.25.25 0 0 0-.063-.407A19.697 19.697 0 0 0 8.91 15.98a.25.25 0 0 0-.287.325c.151.455.334.898.548 1.328.437.827.981 1.594 1.619 2.28a.249.249 0 0 0 .32.044 29.13 29.13 0 0 0 2.506-1.99ZM6.303 14.105a.25.25 0 0 0 .265-.274 13.048 13.048 0 0 1 .205-4.045.062.062 0 0 0-.022-.07 2.5 2.5 0 0 1-.777-.982.25.25 0 0 0-.271-.149 11 11 0 0 0-5.6 2.815.255.255 0 0 0-.075.163c-.008.135-.02.27-.02.406.002.8.084 1.598.246 2.381a.25.25 0 0 0 .303.193 19.924 19.924 0 0 1 5.746-.438ZM9.228 20.914a.25.25 0 0 0 .1-.393 11.53 11.53 0 0 1-1.5-2.22 12.238 12.238 0 0 1-.91-2.465.248.248 0 0 0-.22-.187 18.876 18.876 0 0 0-5.69.33.249.249 0 0 0-.179.336c.838 2.142 2.272 4 4.132 5.353a.254.254 0 0 0 .15.048c1.41-.01 2.807-.282 4.117-.802ZM18.93 12.957l-.005-.008a.25.25 0 0 0-.268-.082 2.21 2.21 0 0 1-.41.081.25.25 0 0 0-.217.2c-.582 2.66-2.127 5.35-5.75 7.843a.248.248 0 0 0-.09.299.25.25 0 0 0 .065.091 28.703 28.703 0 0 0 2.662 2.12.246.246 0 0 0 .209.037c2.579-.701 4.85-2.242 6.456-4.378a.25.25 0 0 0 .048-.189 13.51 13.51 0 0 0-2.7-6.014ZM5.702 7.058a.254.254 0 0 0 .2-.165A2.488 2.488 0 0 1 7.98 5.245a.093.093 0 0 0 .078-.062 19.734 19.734 0 0 1 3.055-4.74.25.25 0 0 0-.21-.41 12.009 12.009 0 0 0-10.4 8.558.25.25 0 0 0 .373.281 12.912 12.912 0 0 1 4.826-1.814ZM10.773 22.052a.25.25 0 0 0-.28-.046c-.758.356-1.55.635-2.365.833a.25.25 0 0 0-.022.48c1.252.43 2.568.65 3.893.65.1 0 .2 0 .3-.008a.25.25 0 0 0 .147-.444c-.526-.424-1.1-.917-1.673-1.465ZM18.744 8.436a.249.249 0 0 0 .15.228 2.246 2.246 0 0 1 1.352 2.054c0 .337-.08.67-.23.972a.25.25 0 0 0 .042.28l.007.009a15.016 15.016 0 0 1 2.52 4.6.25.25 0 0 0 .37.132.25.25 0 0 0 .096-.114c.623-1.464.944-3.039.945-4.63a12.005 12.005 0 0 0-5.78-10.258.25.25 0 0 0-.373.274c.547 2.109.85 4.274.901 6.453ZM9.61 5.38a.25.25 0 0 0 .08.31c.34.24.616.561.8.935a.25.25 0 0 0 .3.127.631.631 0 0 1 .206-.034c2.054.078 4.036.772 5.69 1.991a.251.251 0 0 0 .267.024c.046-.024.093-.047.141-.067a.25.25 0 0 0 .151-.23A29.98 29.98 0 0 0 15.957.764a.25.25 0 0 0-.16-.164 11.924 11.924 0 0 0-2.21-.518.252.252 0 0 0-.215.076A22.456 22.456 0 0 0 9.61 5.38Z" />
                                    </g>
                                </svg>
                            </div>

                            <div class="pt-3 sm:pt-5 flex-1 min-w-0">
                                <h2 class="text-lg sm:text-xl font-semibold text-black dark:text-white break-words">Vibrant Ecosystem</h2>

                                <p class="mt-3 sm:mt-4 text-sm/relaxed break-words">
                                    Laravel's robust library of first-party tools and libraries, such as <a
                                        href="https://forge.laravel.com"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white dark:focus-visible:ring-[#FF2D20]">Forge</a>,
                                    <a href="https://vapor.laravel.com"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white">Vapor</a>,
                                    <a href="https://nova.laravel.com"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white">Nova</a>,
                                    and <a href="https://envoyer.io"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white">Envoyer</a>
                                    help you take your projects to the next level. Pair them with powerful open source
                                    libraries like <a href="https://laravel.com/docs/billing"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white">Cashier</a>,
                                    <a href="https://laravel.com/docs/dusk"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white">Dusk</a>,
                                    <a href="https://laravel.com/docs/broadcasting"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white">Echo</a>,
                                    <a href="https://laravel.com/docs/horizon"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white">Horizon</a>,
                                    <a href="https://laravel.com/docs/sanctum"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white">Sanctum</a>,
                                    <a href="https://laravel.com/docs/telescope"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white">Telescope</a>,
                                    and more.
                                </p>
                            </div>
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    © {{ currentYear }} FanSight.ID - Eksperimental <a href="https://labqitech.my.id" class="underline hover:text-black dark:hover:text-white">LabqiTech</a>, Dikembangkan oleh <a href="https://iqbalarauf.my.id" class="underline hover:text-black dark:hover:text-white">IqbalARauf</a> 
                </footer>
            </div>
        </div>
    </div>
</template>
