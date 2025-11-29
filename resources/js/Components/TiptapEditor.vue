<template>
  <div class="tiptap-editor border rounded-lg dark:border-neutral-700">
    <!-- Toolbar -->
    <div class="border-b dark:border-neutral-700 p-2 flex flex-wrap gap-1 bg-gray-50 dark:bg-neutral-800">
      <button
        type="button"
        @click="editor.chain().focus().toggleBold().run()"
        :class="[baseBtnClasses, editor?.isActive('bold') ? activeBtnClasses : '']"
        title="Bold (Ctrl+B)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
        </svg>
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleItalic().run()"
        :class="[baseBtnClasses, editor?.isActive('italic') ? activeBtnClasses : '']"
        title="Italic (Ctrl+I)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h10M4 20h10m-3-16L7 20"></path>
        </svg>
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleUnderline().run()"
        :class="[baseBtnClasses, editor?.isActive('underline') ? activeBtnClasses : '']"
        title="Underline (Ctrl+U)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3M4 21h16"></path>
        </svg>
      </button>

      <div class="w-px bg-gray-300 dark:bg-neutral-600"></div>

      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
        :class="[baseBtnClasses, editor?.isActive('heading', { level: 1 }) ? activeBtnClasses : '']"
        title="Heading 1"
      >
        H1
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
        :class="[baseBtnClasses, editor?.isActive('heading', { level: 2 }) ? activeBtnClasses : '']"
        title="Heading 2"
      >
        H2
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
        :class="[baseBtnClasses, editor?.isActive('heading', { level: 3 }) ? activeBtnClasses : '']"
        title="Heading 3"
      >
        H3
      </button>

      <div class="w-px bg-gray-300 dark:bg-neutral-600"></div>

      <button
        type="button"
        @click="editor.chain().focus().toggleBulletList().run()"
        :class="[baseBtnClasses, editor?.isActive('bulletList') ? activeBtnClasses : '']"
        title="Bullet List"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path>
        </svg>
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleOrderedList().run()"
        :class="[baseBtnClasses, editor?.isActive('orderedList') ? activeBtnClasses : '']"
        title="Ordered List"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h.01M3 8h.01M3 12h.01M8 4h13M8 8h13M8 12h13M3 16h.01M3 20h.01M8 16h13M8 20h13"></path>
        </svg>
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleBlockquote().run()"
        :class="[baseBtnClasses, editor?.isActive('blockquote') ? activeBtnClasses : '']"
        title="Blockquote"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
        </svg>
      </button>

      <div class="w-px bg-gray-300 dark:bg-neutral-600"></div>

      <button
        type="button"
        @click="setLink"
        :class="[baseBtnClasses, editor?.isActive('link') ? activeBtnClasses : '']"
        title="Add Link"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
        </svg>
      </button>

      <button
        type="button"
        @click="editor.chain().focus().unsetLink().run()"
        :disabled="!editor?.isActive('link')"
        :class="[baseBtnClasses]"
        title="Remove Link"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </button>

      <div class="w-px bg-gray-300 dark:bg-neutral-600"></div>

      <button
        type="button"
        @click="triggerImageUpload"
        :class="[baseBtnClasses]"
        :disabled="uploading"
        :title="uploading ? 'Uploading…' : 'Upload & Insert Image'"
      >
        <svg v-if="!uploading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M4 12a8 8 0 018-8" class="opacity-75" />
        </svg>
      </button>

      <input
        ref="fileInput"
        type="file"
        accept="image/*"
        class="hidden"
        @change="handleFileChange"
      />

      <button
        type="button"
        @click="addYoutubeVideo"
        :class="[baseBtnClasses]"
        title="Embed YouTube Video"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
          <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
        </svg>
      </button>

      <div class="w-px bg-gray-300 dark:bg-neutral-600"></div>

      <button
        type="button"
        @click="editor.chain().focus().undo().run()"
        :disabled="!editor?.can().undo()"
        :class="[baseBtnClasses]"
        title="Undo (Ctrl+Z)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
        </svg>
      </button>

      <button
        type="button"
        @click="editor.chain().focus().redo().run()"
        :disabled="!editor?.can().redo()"
        :class="[baseBtnClasses]"
        title="Redo (Ctrl+Y)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"></path>
        </svg>
      </button>
    </div>

    <!-- Editor Content -->
    <editor-content :editor="editor" class="prose max-w-none p-4 min-h-[300px] dark:prose-invert" />
    <div v-if="uploadError" class="px-4 pb-3 text-sm text-red-600 dark:text-red-400">{{ uploadError }}</div>
  </div>
</template>

<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import Youtube from '@tiptap/extension-youtube';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import { watch, ref } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Link.configure({
      openOnClick: false,
      HTMLAttributes: {
        target: '_blank',
        rel: 'noopener noreferrer',
      },
    }),
    Image.configure({
      inline: true,
      allowBase64: true,
      HTMLAttributes: {
        class: 'max-w-full h-auto rounded',
      },
    }),
    Youtube.configure({
      controls: true,
      nocookie: true,
    }),
    Underline,
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
  ],
  editorProps: {
    attributes: {
      class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-xl focus:outline-none dark:prose-invert max-w-none',
    },
  },
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML());
  },
});

// Reusable toolbar button class strings (removed @apply usage in <style>)
const baseBtnClasses = 'px-2 py-1 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-neutral-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors';
const activeBtnClasses = 'bg-gray-300 dark:bg-neutral-600';

// Watch for external changes to modelValue
watch(() => props.modelValue, (value) => {
  const isSame = editor.value.getHTML() === value;
  if (!isSame) {
    editor.value.commands.setContent(value, false);
  }
});

const setLink = () => {
  const previousUrl = editor.value.getAttributes('link').href;
  const url = window.prompt('URL', previousUrl);

  if (url === null) {
    return;
  }

  if (url === '') {
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
    return;
  }

  editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

// Image upload integration
const uploading = ref(false);
const uploadError = ref('');
const fileInput = ref(null);

const triggerImageUpload = () => {
  uploadError.value = '';
  if (uploading.value) return;
  fileInput.value?.click();
};

const handleFileChange = async (e) => {
  const file = e.target.files?.[0];
  e.target.value = '';
  if (!file) return;
  uploading.value = true;
  uploadError.value = '';
  try {
    const form = new FormData();
    form.append('image', file);
    form.append('folder', 'post-images');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const res = await fetch('/uploads/images', {
      method: 'POST',
      headers: token ? { 'X-CSRF-TOKEN': token } : {},
      body: form,
    });
    if (!res.ok) {
      let data; try { data = await res.json(); } catch (_) {}
      throw new Error(data?.error || 'Upload failed');
    }
    const data = await res.json();
    console.log('Upload success:', data);
    const alt = file.name.replace(/\.[^.]+$/, '');
    // Use exposed helper for consistent insertion structure.
    insertImageAtCursor(data.url, alt);
    console.log('Image inserted, editor HTML:', editor.value?.getHTML().substring(0, 300));
  } catch (err) {
    console.error('Upload error:', err);
    uploadError.value = err.message || 'Unexpected upload error';
  } finally {
    uploading.value = false;
  }
};

const addYoutubeVideo = () => {
  const url = window.prompt('YouTube URL');

  if (url) {
    editor.value.chain().focus().setYoutubeVideo({ src: url }).run();
  }
};

// Expose helper to insert image at current cursor programmatically
function insertImageAtCursor(url, alt = '') {
  if (!url) return;
  const ed = editor.value;
  if (!ed) {
    console.error('Editor not initialized');
    return;
  }
  console.log('Inserting image:', url);
  // Insert image inline within current paragraph, or create new paragraph if needed
  ed.chain()
    .focus()
    .setImage({ src: url, alt })
    .run();
  console.log('After insert, HTML:', ed.getHTML().substring(0, 300));
}

defineExpose({ insertImageAtCursor });
</script>

<style scoped>
:deep(.ProseMirror){min-height:300px;outline:none;}
:deep(.ProseMirror p.is-editor-empty:first-child::before){content:attr(data-placeholder);float:left;color:#adb5bd;pointer-events:none;height:0;}
</style>
