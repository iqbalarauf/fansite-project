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
              <label class="block">Body</label>
              <textarea v-model="form.body" rows="10" class="w-full border px-2 py-1"></textarea>
              <div v-if="form.errors.body" class="text-red-600 text-sm">{{ form.errors.body }}</div>
              <div class="text-sm text-gray-600 mt-2">
                You can use <code>**bold**</code>, <code>*italic*</code>, and links like
                <code>[label](https://example.com)</code>.
                Line breaks are preserved.
              </div>
            </div>

            <div class="mt-4">
              <h3 class="text-sm font-medium mb-2">Live preview</h3>
              <div class="mb-2">
                <img v-if="selectedPreview" :src="selectedPreview" class="w-full" />
              </div>
              <div class="p-4 border rounded bg-white prose" v-html="previewHtml"></div>
            </div>
          </div>

          <!-- Right: Title, excerpt, image, category, tags, status, save -->
          <div class="md:col-span-1 space-y-4">
            <div>
              <label class="block">Title</label>
              <input v-model="form.title" class="w-full border px-2 py-1" />
              <div v-if="form.errors.title" class="text-red-600 text-sm">{{ form.errors.title }}</div>
            </div>

            <div>
              <label class="block">Excerpt</label>
              <input v-model="form.excerpt" class="w-full border px-2 py-1" />
            </div>

            <div>
              <label class="block">Featured image</label>
              <input type="file" @change="onFileChange" />
              <div class="text-red-600 text-sm pt-1">*Max File Size: 2048 KB/2 MB</div>
            </div>

            <div>
              <label class="block">Category</label>
              <input v-model="form.category" class="w-full border px-2 py-1" />
            </div>

            <div>
              <label class="block">Tags (comma separated)</label>
              <input v-model="form.tags" class="w-full border px-2 py-1" />
            </div>

            <div>
              <label class="block">Status</label>
              <select v-model="form.status" class="border px-2 py-1 w-full">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
              </select>
            </div>

            <div>
              <button :disabled="form.processing" class="w-full px-4 py-2 bg-blue-600 text-white rounded">
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
import { computed, ref, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

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
  form.post(route('posts.store'), {
    forceFormData: true,
    onSuccess: () => {
      // show success briefly then navigate to posts.manage
      success.value = 'Post created.';
      setTimeout(() => Inertia.visit(route('posts.manage')), 700);
    }
  });
};

// Simple client-side preview that mirrors the server-side formatting rules.
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
