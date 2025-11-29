<template>
  <AppLayout title="Create Post">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Create New Post
        </h2>
      </div>
    </template>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Left: Body + Preview (spans 2) -->
          <div class="md:col-span-2">
            <div class="mb-4">
              <label class="block mb-2 text-sm font-medium dark:text-white">Title</label>
              <input v-model="form.title" type="text"
                class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                placeholder="Title" />
              <div v-if="form.errors.title" class="text-red-600 text-sm">{{ form.errors.title }}</div>
            </div>

            <div class="mb-4">
              <label class="block mb-2 text-sm font-medium dark:text-white">Body</label>
              <TiptapEditor v-model="form.body" />
              <div v-if="form.errors.body" class="text-red-600 text-sm mt-2">{{ form.errors.body }}</div>
            </div>
          </div>

          <!-- Right: Title, excerpt, image, category, tags, status, save -->
          <div class="md:col-span-1 space-y-4">
            <div class="mb-4">
              <label class="block">Featured image</label>
              <div class="mb-2">
                <img v-if="selectedPreview" :src="selectedPreview" class="w-full h-36 object-cover rounded" />
              </div>
              <input type="file" @change="onFileChange" />
              <div class="text-red-600 text-sm pt-1">*Max File Size: 2048 KB/2 MB</div>
            </div>

            <div class="mb-4 sm:mb-8">
              <label class="block mb-2 text-sm font-medium dark:text-white">Category</label>
              <input v-model="form.category" type="text"
                class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                placeholder="Category" />
            </div>

            <div class="mb-4 sm:mb-8">
              <label class="block mb-2 text-sm font-medium dark:text-white">Tags (comma separated)</label>
              <input v-model="form.tags" type="text"
                class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                placeholder="Tags (comma separated)" />
            </div>

            <div class="mb-4 sm:mb-8">
              <label class="block mb-2 text-sm font-medium dark:text-white">Status</label>
              <select v-model="form.status" class="border px-2 py-1 w-full">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
              </select>
            </div>

            <div class="mt-6 grid">
              <button :disabled="form.processing" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                <span v-if="form.processing">Saving…</span>
                <span v-else>Save</span>
              </button>
              <div class="mt-2">
                <ActionMessage :on="!!success" class="text-sm text-green-600">{{ success }}</ActionMessage>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import { ref, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue';

const form = useForm({
  title: '',
  excerpt: '',
  body: '',
  featured_image: null,
  category: '',
  tags: '',
  status: 'draft',
  published_at: null,
});

const selectedPreview = ref(null);

const onFileChange = (e) => {
  const file = e.target.files?.[0] ?? null;
  form.featured_image = file;

  // free previous object url if any
  if (selectedPreview.value) {
    try { URL.revokeObjectURL(selectedPreview.value); } catch (e) { }
    selectedPreview.value = null;
  }
  if (file) {
    selectedPreview.value = URL.createObjectURL(file);
  }
};

onUnmounted(() => {
  if (selectedPreview.value) {
    try { URL.revokeObjectURL(selectedPreview.value); } catch (e) { }
  }
});

const success = ref('');

const submit = () => {
  console.log('Submitting form.body:', form.body?.substring(0, 200));
  form.post(route('posts.store'), {
    forceFormData: true,
    onSuccess: () => {
      // show success briefly then navigate to posts.manage
      success.value = 'Post created.';
      setTimeout(() => Inertia.visit(route('posts.manage')), 700);
    }
  });
};

// TiptapEditor now handles inline image uploads internally.
</script>
