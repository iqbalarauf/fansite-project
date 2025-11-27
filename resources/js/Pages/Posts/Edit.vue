<template>
  <AppLayout title="Edit Post">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Edit Post
      </h2>
    </template>

    <!-- make 2 column, col-span-2 in left filled with Body and Live Preview, col-span-1 in right filled with Title, Excerpt, Current featured image, category, tags, and Save Button -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="col-span-2">
            <div>
              <label class="block mb-2 text-sm font-medium dark:text-white">Body</label>
              <textarea rows="3"
                class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                placeholder="Write your article here" v-model="form.body"></textarea>
              <div v-if="form.errors.body" class="text-red-600 text-sm">{{ form.errors.body }}</div>
            </div>

            <div class="text-sm text-gray-600 dark:text-white mt-2">
              You can use <code>**bold**</code>, <code>*italic*</code>, and links like
              <code>[label](https://example.com)</code>.
              Line breaks are preserved.
            </div>

            <div class="my-6 sm:mb-8">
              <h3 class="text-sm font-medium mb-2 dark:text-white">Live preview</h3>
              <div class="mb-2">
                <img v-if="selectedPreview" :src="selectedPreview" class="w-full" />
                <img v-else-if="post.featured_image" :src="post.featured_image" class="w-full" />
              </div>
              <div class="p-4 border rounded dark:text-neutral-400" v-html="previewHtml"></div>
            </div>
          </div>
          <div class="md:col-span-1">
            <div class="mb-4 sm:mb-8">
              <label class="block mb-2 text-sm font-medium dark:text-white">Title</label>
              <input type="text"
                class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                v-model="form.title" placeholder="Title">
              <div v-if="form.errors.title" class="text-red-600 text-sm">{{ form.errors.title }}</div>
            </div>

            <div class="mb-4 sm:mb-8">
              <label class="block mb-2 text-sm font-medium dark:text-white">Excerpt</label>
              <input type="text"
                class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                placeholder="Excerpt" v-model="form.excerpt">
            </div>

            <div class="mb-4">
              <label class="block">Current featured image</label>
              <input type="file" @change="onFileChange" />
              <div class="text-red-600 text-sm pt-1">*Max File Size: 2048 KB/2 MB</div>
            </div>
            
            <!-- moved right-column fields here so the right column contains title, excerpt, image, category, tags, status and save -->
            <div class="mb-4 sm:mb-8">
              <label class="block mb-2 text-sm font-medium dark:text-white">Category</label>
              <input type="text"
                class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                placeholder="Category" v-model="form.category">
            </div>

            <div class="mb-4 sm:mb-8">
              <label class="block mb-2 text-sm font-medium dark:text-white">Tags (comma separated)</label>
              <input type="text"
                class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                placeholder="Category" v-model="form.tags">
            </div>

            <div class="mb-4">
              <label class="block">Status</label>
              <select v-model="form.status" class="border px-2 py-1 w-full">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
              </select>
            </div>

            <div class="mt-6 grid">
              <button :disabled="form.processing" type="submit"
                class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
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
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Dropdown from '@/Components/Dropdown.vue';

const props = defineProps({
  post: Object,
});

const post = props.post;

const form = useForm({
  // include _method so FormData contains it when we call form.post (important for file uploads)
  _method: 'PUT',
  title: post.title || '',
  excerpt: post.excerpt || '',
  body: post.body || '',
  featured_image: null,
  category: post.category || '',
  tags: Array.isArray(post.tags) ? post.tags.join(',') : (post.tags || ''),
  status: post.status || 'draft',
  published_at: post.published_at || null,
});

const selectedPreview = ref(null);

const onFileChange = (e) => {
  const file = e.target.files?.[0] ?? null;
  form.featured_image = file;
  // free previous object url if any
  if (selectedPreview.value) {
    try { URL.revokeObjectURL(selectedPreview.value); } catch (e) {}
    selectedPreview.value = null;
  }
  if (file) {
    selectedPreview.value = URL.createObjectURL(file);
  }
};

onUnmounted(() => {
  if (selectedPreview.value) {
    try { URL.revokeObjectURL(selectedPreview.value); } catch (e) {}
  }
});

const success = ref('');

const submit = () => {
  // Post with _method supplied in form data so PHP/Laravel can parse multipart fields
  form.post(route('posts.update', post.slug), {
    forceFormData: true,
    onSuccess: () => {
      success.value = 'Post updated.';
      // delay navigation briefly so the user can see the success message
      setTimeout(() => Inertia.visit(route('posts.manage')), 700);
    },
  });
};

const escapeHtml = (str) =>
  String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const previewHtml = computed(() => {
  let text = form.body || '';
  text = escapeHtml(text);
  text = text.replace(/\[(.*?)\]\((https?:\/\/[^)\s]+)\)/gi, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
  text = text.replace(/\*\*(.*?)\*\*/gs, '<strong>$1</strong>');
  text = text.replace(/\*(.*?)\*/gs, '<em>$1</em>');
  text = text.replace(/\n/g, '<br>');
  return text;
});
</script>
