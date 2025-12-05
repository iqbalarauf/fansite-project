<template>
  <Head :title="title" />

  <AppLayout title="Manage Pages">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Manage Custom Pages
        </h2>
      </div>
    </template>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-end mb-6">
        <Link :href="route('pages.create')" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
          Create Page
        </Link>
      </div>

      <div v-if="pagesList && pagesList.length">
        <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
          <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
            <div class="w-full md:w-1/2">
              <form class="flex items-center" @submit.prevent>
                <label for="search" class="sr-only">Search</label>
                <div class="relative w-full">
                  <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <input id="search" v-model="searchTerm" type="text" placeholder="Search title..." class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                </div>
              </form>
            </div>
            <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
              <select v-model="selectedStatus" class="w-full md:w-auto flex items-center justify-center py-2 px-6 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                <option value="all">Status</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
              </select>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                  <th scope="col" class="px-4 py-3">Title</th>
                  <th scope="col" class="px-4 py-3">Published Date</th>
                  <th scope="col" class="px-4 py-3">Status</th>
                  <th scope="col" class="px-4 py-3">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="page in paginatedPages" :key="page.id" class="border-b dark:border-gray-700">
                  <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    <Link :href="route('pages.show', page.slug)">{{ page.title }}</Link>
                  </th>
                  <td class="px-4 py-3">{{ page.published_at ? formatDate(page.published_at) : '-' }}</td>
                  <td class="px-4 py-3">{{ page.status }}</td>
                  <td class="px-4 py-3 flex items-center justify-end">
                    <Link :href="route('pages.edit', page.slug)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</Link>
                    <button @click.prevent="destroy(page.slug)" :disabled="deleting === page.slug" :class="deleting === page.slug ? 'text-red-300 cursor-not-allowed' : 'text-red-600 hover:text-red-800'">
                      <span v-if="deleting === page.slug">Deleting…</span>
                      <span v-else>Delete</span>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <nav v-if="filteredPages.length > itemsPerPage" class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4" aria-label="Table navigation">
            <ul class="inline-flex items-stretch -space-x-px">
              <li>
                <button :disabled="currentPage === 1" @click="prevPage" class="flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed">
                  <span class="sr-only">Previous</span>
                  <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </li>
              <span class="text-sm font-normal text-gray-500 dark:text-gray-400 px-3 py-1.5">
                Showing
                <span class="font-semibold text-gray-900 dark:text-white">{{ currentPage }}</span>
                of
                <span class="font-semibold text-gray-900 dark:text-white">{{ totalPages }}</span>
              </span>
              <li>
                <button :disabled="currentPage === totalPages" @click="nextPage" class="flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed">
                  <span class="sr-only">Next</span>
                  <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>

      <div v-else class="text-gray-600">
        You have no pages yet.
        <Link :href="route('pages.create')" class="text-blue-600">Create one</Link>.
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDate } from '@/Helpers/formatDate';
import AppLayout from '@/Layouts/AppLayout.vue';

const title = 'Manage Pages';
const props = defineProps({
  pages: Object,
});

const pagesList = ref(props.pages?.data ? [...props.pages.data] : []);
const pagesMeta = ref(props.pages ? { ...props.pages } : { prev_page_url: null, next_page_url: null, total: 0, per_page: 20, current_page: 1 });
const deleting = ref(null);
const error = ref('');
const success = ref('');

watch(() => props.pages, (p) => {
  pagesList.value = p?.data ? [...p.data] : [];
  pagesMeta.value = p ? { ...p } : pagesMeta.value;
});

const destroy = (slug) => {
  if (!confirm('Delete this page?')) return;

  const index = pagesList.value.findIndex(p => p.slug === slug || p.id === slug);
  if (index === -1) return;

  const removed = pagesList.value.splice(index, 1)[0];
  deleting.value = slug;

  pagesMeta.value.total = Math.max(0, (pagesMeta.value.total || 0) - 1);
  const perPage = pagesMeta.value.per_page || 20;
  const totalPages = Math.max(1, Math.ceil(pagesMeta.value.total / perPage));
  if ((pagesMeta.value.current_page || 1) >= totalPages) {
    pagesMeta.value.next_page_url = null;
  }

  router.delete(route('pages.destroy', slug), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      // Scroll to top to show notification
      window.scrollTo({ top: 0, behavior: 'smooth' });

      success.value = 'Page deleted.';
      deleting.value = null;

      if (pagesList.value.length === 0 && pagesMeta.value.prev_page_url) {
        setTimeout(() => router.visit(pagesMeta.value.prev_page_url), 300);
      }
    },
    onError: () => {
      // Scroll to top to show error notification
      window.scrollTo({ top: 0, behavior: 'smooth' });

      pagesList.value.splice(index, 0, removed);
      deleting.value = null;
      error.value = 'Failed to delete page — please try again.';
      setTimeout(() => error.value = '', 3000);
    },
  });
};

const searchTerm = ref('');
const selectedStatus = ref('all');
const itemsPerPage = 10;
const currentPage = ref(1);

const filteredPages = computed(() => {
  const term = (searchTerm.value || '').toLowerCase().trim();
  return pagesList.value.filter(p => {
    const matchesTitle = !term || (p.title || '').toLowerCase().includes(term);
    const status = (p.status || '').toLowerCase();
    const matchesStatus = selectedStatus.value === 'all' || status === selectedStatus.value;
    return matchesTitle && matchesStatus;
  });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredPages.value.length / itemsPerPage)));

const paginatedPages = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredPages.value.slice(start, start + itemsPerPage);
});

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value -= 1;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value += 1;
};

watch([searchTerm, selectedStatus], () => currentPage.value = 1);
</script>
