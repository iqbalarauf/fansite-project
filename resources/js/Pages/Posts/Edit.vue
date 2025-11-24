<template>
  <div class="max-w-3xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Edit Post</h1>

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
        <label class="block">Current featured image</label>
        <div v-if="post.featured_image" class="mb-2"><img :src="post.featured_image" class="w-48" /></div>
        <input type="file" @change="onFileChange" />
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
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  post: Object,
});

const post = props.post;

const form = useForm({
  title: post.title || '',
  excerpt: post.excerpt || '',
  body: post.body || '',
  featured_image: null,
  category: post.category || '',
  tags: Array.isArray(post.tags) ? post.tags.join(',') : (post.tags || ''),
  status: post.status || 'draft',
  published_at: post.published_at || null,
});

const onFileChange = (e) => {
  form.featured_image = e.target.files[0];
};

const submit = () => {
  form.post(route('posts.update', post.slug), {
    method: 'put',
    forceFormData: true,
    onSuccess: () => Inertia.visit(route('posts.manage')),
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
