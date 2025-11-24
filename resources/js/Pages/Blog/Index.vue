<template>
<Head :title="title" />

  <div class="max-w-4xl mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Blog</h1>

    <div v-if="posts && posts.data.length">
      <div v-for="post in posts.data" :key="post.id" class="mb-6 border-b pb-4">
        <h2 class="text-xl font-semibold">
          <Link :href="route('blog.show', post.slug)">{{ post.title }}</Link>
        </h2>
        <p class="text-sm text-gray-600">Published: {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : '-' }}</p>
        <p class="mt-2">{{ post.excerpt }}</p>
      </div>

      <div class="mt-6">
        <!-- simple pagination controls -->
        <div class="flex justify-between items-center">
          <div>
            <button v-if="posts.prev_page_url" @click="visit(posts.prev_page_url)" class="px-3 py-1 bg-gray-200 rounded">Previous</button>
          </div>
          <div>
            <button v-if="posts.next_page_url" @click="visit(posts.next_page_url)" class="px-3 py-1 bg-gray-200 rounded">Next</button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-gray-600">No posts found.</div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  posts: Object,
  title: String,
});

const visit = (url) => {
  Inertia.visit(url);
};
</script>
