<template>
    <Head :title="post.title" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div class="max-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="sticky top-0 z-50 left-0 right-0 w-full bg-white/60 dark:bg-black/60 backdrop-blur-sm">
                <div class="max-w-7xl mx-auto px-6">
                    <SiteHeader :can-login="canLogin" :can-register="canRegister" />
                </div>
            </div>

            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <div class="max-w-3xl mx-auto py-8">
                    <h1 class="text-3xl font-bold mb-2">{{ post.title }}</h1>
                    <p class="text-sm text-gray-600 mb-4">By {{ post.user.name }} • {{ post.published_at ?
                        formatDate(post.published_at) : '-' }}</p>

                    <img v-if="post.featured_image" :src="post.featured_image" alt="" class="w-full mb-4 rounded" />

                    <div class="prose" v-html="post.body_html"></div>
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
