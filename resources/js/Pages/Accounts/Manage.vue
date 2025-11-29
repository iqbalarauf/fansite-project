<template>
  <Head :title="title" />
  <AppLayout :title="title">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Manage Accounts
        </h2>
      </div>
    </template>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-4">
        <ActionMessage :on="!!success" class="text-sm text-green-600">{{ success }}</ActionMessage>
        <div v-if="error" class="text-sm text-red-600 mt-1">{{ error }}</div>
      </div>

      <div class="flex justify-end mb-6">
        <button @click="showCreateModal = true" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
          Create Account
        </button>
      </div>

      <div v-if="usersList && usersList.length">
        <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
          <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
            <div class="w-full md:w-1/2">
              <form class="flex items-center" @submit.prevent>
                <label class="sr-only" for="search">Search</label>
                <div class="relative w-full">
                  <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" />
                    </svg>
                  </div>
                  <input id="search" v-model="searchTerm" placeholder="Search name or email..." type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                </div>
              </form>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                  <th class="px-4 py-3" scope="col">Name</th>
                  <th class="px-4 py-3" scope="col">Email</th>
                  <th class="px-4 py-3" scope="col">Created</th>
                  <th class="px-4 py-3" scope="col">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="u in paginatedUsers" :key="u.id" class="border-b dark:border-gray-700">
                  <th class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white" scope="row">
                    {{ u.name || '-' }}
                  </th>
                  <td class="px-4 py-3">{{ u.email }}</td>
                  <td class="px-4 py-3">{{ u.created_at ? formatDate(u.created_at) : '-' }}</td>
                  <td class="px-4 py-3 flex items-center justify-end">
                    <button @click.prevent="destroy(u.id)" :disabled="deleting === u.id" :class="deleting === u.id ? 'text-red-300 cursor-not-allowed' : 'text-red-600 hover:text-red-800'">
                      <span v-if="deleting === u.id">Deleting…</span>
                      <span v-else>Delete</span>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <nav aria-label="Table navigation" class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4">
            <ul class="inline-flex items-stretch -space-x-px">
              <li>
                <button :disabled="currentPage === 1" @click="prevPage" class="flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                  <span class="sr-only">Previous</span>
                  <svg aria-hidden="true" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" />
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
                <button :disabled="currentPage === totalPages" @click="nextPage" class="flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                  <span class="sr-only">Next</span>
                  <svg aria-hidden="true" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" />
                  </svg>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>

      <div v-else class="text-gray-600">
        No accounts found.
        <button @click="showCreateModal = true" class="text-blue-600 hover:underline">Create one</button>.
      </div>
    </div>

    <!-- Create Account Modal -->
    <DialogModal :show="showCreateModal" @close="closeCreateModal">
      <template #title>
        Create New Account
      </template>

      <template #content>
        <form @submit.prevent="submitCreateAccount">
          <div class="space-y-4">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />
              <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
            </div>

            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />
              <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
            </div>

            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />
              <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</div>
            </div>

            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />
            </div>
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeCreateModal">
          Cancel
        </SecondaryButton>

        <PrimaryButton
          class="ml-3"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="submitCreateAccount"
        >
          Create Account
        </PrimaryButton>
      </template>
    </DialogModal>
  </AppLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ActionMessage from '@/Components/ActionMessage.vue';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatDate } from '@/Helpers/formatDate';

const title = 'Manage Accounts';

const props = defineProps({
  users: Object,
});

const usersList = ref(props.users?.data ? [...props.users.data] : []);
const usersMeta = ref(props.users ? { ...props.users } : { prev_page_url: null, next_page_url: null, total: 0, per_page: 20, current_page: 1 });
const deleting = ref(null);
const error = ref('');
const success = ref('');
const showCreateModal = ref(false);

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: true,
});

watch(() => props.users, (p) => {
  usersList.value = p?.data ? [...p.data] : [];
  usersMeta.value = p ? { ...p } : usersMeta.value;
});

const destroy = (id) => {
  if (!confirm('Delete this account?')) return;
  const index = usersList.value.findIndex(u => u.id === id);
  if (index === -1) return;
  const removed = usersList.value.splice(index, 1)[0];
  deleting.value = id;
  usersMeta.value.total = Math.max(0, (usersMeta.value.total || 0) - 1);
  const perPage = usersMeta.value.per_page || 20;
  const totalPages = Math.max(1, Math.ceil(usersMeta.value.total / perPage));
  if ((usersMeta.value.current_page || 1) >= totalPages) {
    usersMeta.value.next_page_url = null;
  }

  router.delete(route('accounts.destroy', id), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      success.value = 'Account deleted.';
      deleting.value = null;
      if (usersList.value.length === 0 && usersMeta.value.prev_page_url) {
        setTimeout(() => router.visit(usersMeta.value.prev_page_url), 300);
      }
    },
    onError: () => {
      usersList.value.splice(index, 0, removed);
      deleting.value = null;
      error.value = 'Failed to delete account — please try again.';
      setTimeout(() => (error.value = ''), 3000);
    },
  });
};

const searchTerm = ref('');
const itemsPerPage = 10;
const currentPage = ref(1);

const filteredUsers = computed(() => {
  const term = (searchTerm.value || '').toLowerCase().trim();
  return usersList.value.filter(u => {
    const name = (u.name || '').toLowerCase();
    const email = (u.email || '').toLowerCase();
    return !term || name.includes(term) || email.includes(term);
  });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / itemsPerPage)));

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredUsers.value.slice(start, start + itemsPerPage);
});

const prevPage = () => { if (currentPage.value > 1) currentPage.value -= 1; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value += 1; };

watch([searchTerm], () => (currentPage.value = 1));

const closeCreateModal = () => {
  showCreateModal.value = false;
  form.reset();
  form.clearErrors();
};

const submitCreateAccount = () => {
  form.post(route('register'), {
    preserveScroll: true,
    onSuccess: () => {
      closeCreateModal();
      success.value = 'Account created successfully.';
      setTimeout(() => {
        success.value = '';
      }, 3000);
    },
    onError: () => {
      // Errors will be displayed in the form
    },
  });
};
</script>
