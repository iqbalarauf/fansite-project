<template>

  <Head title="Blog" />
  <div class="bg-gray-100 dark:bg-gray-900 text-black/50 dark:text-white/50 min-h-screen flex flex-col">
    <div class="sticky top-0 z-50 left-0 right-0 w-full bg-white dark:bg-gray-800 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-6">
        <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
          <Link href="/" class="flex lg:justify-left lg:col-start-1">
            <img src="/storage/logo.svg" class="h-12 w-auto lg:h-16 object-contain" alt="default logo" />
          </Link>

          <nav class="-mx-3 flex items-center gap-2 justify-end justify-self-end lg:col-start-3">
            <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">Dashboard</Link>

            <template v-else>
              <Link :href="route('blog.index')" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">Blog</Link>
            </template>
            <ThemeToggle variant="desktop" />
          </nav>
        </header>
      </div>
    </div>

    <div
      class="flex-grow relative flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
      <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl flex-grow">
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

            <!-- Pagination -->
            <div class="mt-8 mb-6">
              <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <!-- Previous Button -->
                <div>
                  <Link
                    v-if="posts.prev_page_url"
                    :href="posts.prev_page_url"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                  </Link>
                  <span
                    v-else
                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-400 dark:text-gray-600 uppercase tracking-widest cursor-not-allowed"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                  </span>
                </div>

                <!-- Page Info -->
                <div class="text-sm text-gray-700 dark:text-gray-300">
                  <span class="font-medium">Page {{ posts.current_page }}</span>
                  <span class="mx-1">of</span>
                  <span class="font-medium">{{ posts.last_page }}</span>
                  <span class="mx-2">•</span>
                  <span>{{ posts.total }} article{{ posts.total !== 1 ? 's' : '' }}</span>
                </div>

                <!-- Next Button -->
                <div>
                  <Link
                    v-if="posts.next_page_url"
                    :href="posts.next_page_url"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                  >
                    Next
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </Link>
                  <span
                    v-else
                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-400 dark:text-gray-600 uppercase tracking-widest cursor-not-allowed"
                  >
                    Next
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-gray-600">No posts found.</div>
        </main>
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
import { Head, Link } from '@inertiajs/vue3';
import { formatDate } from '@/Helpers/formatDate';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
  posts: Object,
  title: String,
});

const title = "Blog";
</script>
