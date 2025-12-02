<template>

    <Head>
        <title>{{ post.meta_title || post.title }}</title>
        <meta name="description" :content="post.meta_description || post.excerpt || ''" />
        <meta name="keywords" :content="post.meta_keywords || ''" v-if="post.meta_keywords" />
        <meta property="og:title" :content="post.meta_title || post.title" />
        <meta property="og:description" :content="post.meta_description || post.excerpt || ''" />
        <meta property="og:image" :content="post.featured_image || ''" v-if="post.featured_image" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="post.meta_title || post.title" />
        <meta name="twitter:description" :content="post.meta_description || post.excerpt || ''" />
        <meta name="twitter:image" :content="post.featured_image || ''" v-if="post.featured_image" />
    </Head>
    <div class="bg-gray-100 dark:bg-gray-900 text-black/50 dark:text-white/50 min-h-screen flex flex-col">
        <!-- put the sticky header before the centered content so it remains at the top -->
        <div class="sticky top-0 z-50 left-0 right-0 w-full bg-white dark:bg-gray-800 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-6">
                <SiteHeader />
            </div>
        </div>

        <div class="flex-grow flex flex-col items-center justify-start selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl flex-grow">
                <div class="max-w-3xl mx-auto py-8">
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-2">{{ post.title }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">By {{ post.user.name }} • {{
                        post.published_at ?
                            formatDate(post.published_at) : '-' }}</p>

                    <img v-if="post.featured_image" :src="post.featured_image" alt="" class="w-full mb-4 rounded" />

                        <div class="prose max-w-none dark:prose-invert" v-html="post.body"></div>
                </div>
            </div>
        </div>

        <div class="w-full">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl mx-auto">
                <Footer type="public" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { formatDate } from '@/Helpers/formatDate';
import SiteHeader from '@/Components/SiteHeader.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    post: Object,
    title: String,
});

const post = props.post;
</script>
