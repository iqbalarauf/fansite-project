<template>

  <Head title="Blog" />
  <div class="bg-gray-100 dark:bg-gray-900 text-black/50 dark:text-white/50">
    <div class="sticky top-0 z-50 left-0 right-0 w-full bg-white dark:bg-gray-800 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-6">
        <SiteHeader />
      </div>
    </div>

    <div
      class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
      <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
        <main class="mt-6">
          <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-white">Blog</h1>

          <div v-if="posts && posts.data.length">
              <div v-for="post in posts.data" :key="post.id" class="mb-6 border p-6 rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start gap-4">
                  <template v-if="post.thumbnail">
                    <img :src="post.thumbnail" alt="thumbnail" class="hidden sm:block w-24 h-24 rounded-md object-cover shrink-0" />
                  </template>

                  <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                      <Link :href="route('blog.show', post.slug)" class="block truncate">{{ post.title }}</Link>
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">Published: {{ post.published_at ? formatDate(post.published_at) : '-' }}</p>

                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                      <!-- show excerpt if provided, otherwise use the summary trimmed by the server -->
                      {{ post.excerpt ?? post.summary }}

                      <template v-if="post.has_more">
                        <Link :href="route('blog.show', post.slug)" class="text-sm text-gray-500 dark:text-gray-400 underline ms-2">Read More</Link>
                      </template>
                    </p>
                  </div>
                </div>
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
