<template>

    <Head :title="post.title" />
    <div class="bg-gray-100 dark:bg-gray-900 text-black/50 dark:text-white/50">
        <!-- put the sticky header before the centered content so it remains at the top -->
        <div class="sticky top-0 z-50 left-0 right-0 w-full bg-white dark:bg-gray-800 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-6">
                <SiteHeader />
            </div>
        </div>

        <div class="min-h-screen flex flex-col items-center justify-start selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
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
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { formatDate } from '@/Helpers/formatDate';
import SiteHeader from '@/Components/SiteHeader.vue';

const props = defineProps({
    post: Object,
    title: String,
});

const post = props.post;
</script>
