<template>
  <div class="max-w-3xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Create Post</h1>

    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block">Title</label>
        <input v-model="form.title" class="w-full border px-2 py-1" />
        <div v-if="form.errors.title" class="text-red-600 text-sm">{{ form.errors.title }}</div>
      </div>

      <div class="mb-4">
        <label class="block">Excerpt</label>
        <input v-model="form.excerpt" class="w-full border px-2 py-1" />
      </div>

      <div class="mb-4">
        <label class="block">Body</label>
        <textarea v-model="form.body" rows="8" class="w-full border px-2 py-1"></textarea>
        <div v-if="form.errors.body" class="text-red-600 text-sm">{{ form.errors.body }}</div>
        <div class="text-sm text-gray-600 mt-2">
          You can use <code>**bold**</code>, <code>*italic*</code>, and links like <code>[label](https://example.com)</code>.
          Line breaks are preserved.
        </div>

        <div class="mt-4">
          <h3 class="text-sm font-medium mb-2">Live preview</h3>
          <div class="p-4 border rounded bg-white prose" v-html="previewHtml"></div>
        </div>
      </div>

      <div class="mb-4">
        <label class="block">Featured image</label>
        <input type="file" @change="onFileChange" />
        <div class="text-red-600 text-sm pt-1">*Max File Size: 2048 KB/2 MB</div>
      </div>

      <div class="mb-4">
        <label class="block">Category</label>
        <input v-model="form.category" class="w-full border px-2 py-1" />
      </div>

      <div class="mb-4">
        <label class="block">Tags (comma separated)</label>
        <input v-model="form.tags" class="w-full border px-2 py-1" />
      </div>

      <div class="mb-4">
        <label class="block">Status</label>
        <select v-model="form.status" class="border px-2 py-1">
          <option value="draft">Draft</option>
          <option value="published">Published</option>
        </select>
      </div>

      <div class="mb-4">
        <button :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded">
          <span v-if="form.processing">Saving…</span>
          <span v-else>Save</span>
        </button>
        <div class="mt-2">
          <ActionMessage :on="!!success" class="text-sm text-green-600">{{ success }}</ActionMessage>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import { computed, ref } from 'vue';

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

const onFileChange = (e) => {
  form.featured_image = e.target.files[0];
};

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

