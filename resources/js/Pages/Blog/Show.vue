<template>
    <Head :title="post.title" />

  <div class="max-w-3xl mx-auto py-8">
    <h1 class="text-3xl font-bold mb-2">{{ post.title }}</h1>
    <p class="text-sm text-gray-600 mb-4">By {{ post.user.name }} • {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : '-' }}</p>

    <img v-if="post.featured_image" :src="post.featured_image" alt="" class="w-full mb-4 rounded" />

    <div class="prose" v-html="post.body_html"></div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  post: Object,
  title: String,
});

const post = props.post;
</script>
