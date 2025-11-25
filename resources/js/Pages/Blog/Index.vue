<template>
  <Head title="Blog" />
  <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
    <div
      class="relative max-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
      <!-- Sticky full-width header above the content (so it starts at left:0) -->
      <div class="sticky top-0 z-50 left-0 right-0 w-full bg-white/60 dark:bg-black/60 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-6">
          <SiteHeader />
        </div>
      </div>

      <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
        <main class="mt-6">
          <h1 class="text-3xl font-bold mb-6">Blog</h1>

          <div v-if="posts && posts.data.length">
            <div v-for="post in posts.data" :key="post.id" class="mb-6 border-b pb-4">
              <h2 class="text-xl font-semibold">
                <Link :href="route('blog.show', post.slug)">{{ post.title }}</Link>
              </h2>
              <p class="text-sm text-gray-600">Published: {{ post.published_at ? formatDate(post.published_at) : '-' }}
              </p>
              <p class="mt-2">{{ post.excerpt }}</p>
            </div>

            <div class="mt-6">
              <!-- simple pagination controls -->
              <div class="flex justify-between items-center">
                <div>
                  <button v-if="posts.prev_page_url" @click="visit(posts.prev_page_url)"
                    class="px-3 py-1 bg-gray-200 rounded">Previous</button>
                </div>
                <div>
                  <button v-if="posts.next_page_url" @click="visit(posts.next_page_url)"
                    class="px-3 py-1 bg-gray-200 rounded">Next</button>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-gray-600">No posts found.</div>
          </main>
      </div>
  </div>
</div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { formatDate } from '@/Helpers/formatDate';
import SiteHeader from '@/Components/SiteHeader.vue';

const props = defineProps({
  posts: Object,
  title: String,
});

const visit = (url) => {
  Inertia.visit(url);
};
const title = "Blog";
</script>
