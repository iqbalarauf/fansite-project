<template>
  <div class="tiptap-editor border rounded-lg dark:border-neutral-700">
    <!-- Toolbar -->
    <div class="border-b dark:border-neutral-700 p-2 flex flex-wrap gap-1 bg-gray-50 dark:bg-neutral-800">
      <button
        type="button"
        @click="editor.chain().focus().toggleBold().run()"
        :class="{ 'is-active': editor?.isActive('bold') }"
        class="toolbar-btn"
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
        :class="{ 'is-active': editor?.isActive('italic') }"
        class="toolbar-btn"
        title="Italic (Ctrl+I)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h10M4 20h10m-3-16L7 20"></path>
        </svg>
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleUnderline().run()"
        :class="{ 'is-active': editor?.isActive('underline') }"
        class="toolbar-btn"
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
        :class="{ 'is-active': editor?.isActive('heading', { level: 1 }) }"
        class="toolbar-btn"
        title="Heading 1"
      >
        H1
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
        :class="{ 'is-active': editor?.isActive('heading', { level: 2 }) }"
        class="toolbar-btn"
        title="Heading 2"
      >
        H2
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
        :class="{ 'is-active': editor?.isActive('heading', { level: 3 }) }"
        class="toolbar-btn"
        title="Heading 3"
      >
        H3
      </button>

      <div class="w-px bg-gray-300 dark:bg-neutral-600"></div>

      <button
        type="button"
        @click="editor.chain().focus().toggleBulletList().run()"
        :class="{ 'is-active': editor?.isActive('bulletList') }"
        class="toolbar-btn"
        title="Bullet List"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path>
        </svg>
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleOrderedList().run()"
        :class="{ 'is-active': editor?.isActive('orderedList') }"
        class="toolbar-btn"
        title="Ordered List"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h.01M3 8h.01M3 12h.01M8 4h13M8 8h13M8 12h13M3 16h.01M3 20h.01M8 16h13M8 20h13"></path>
        </svg>
      </button>

      <button
        type="button"
        @click="editor.chain().focus().toggleBlockquote().run()"
        :class="{ 'is-active': editor?.isActive('blockquote') }"
        class="toolbar-btn"
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
        :class="{ 'is-active': editor?.isActive('link') }"
        class="toolbar-btn"
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
        class="toolbar-btn"
        title="Remove Link"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </button>

      <div class="w-px bg-gray-300 dark:bg-neutral-600"></div>

      <button
        type="button"
        @click="addImage"
        class="toolbar-btn"
        title="Insert Image"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
      </button>

      <button
        type="button"
        @click="addYoutubeVideo"
        class="toolbar-btn"
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
        class="toolbar-btn"
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
        class="toolbar-btn"
        title="Redo (Ctrl+Y)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"></path>
        </svg>
      </button>
    </div>

    <!-- Editor Content -->
    <editor-content :editor="editor" class="prose max-w-none p-4 min-h-[300px] dark:prose-invert" />
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
import { watch } from 'vue';

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
    StarterKit.configure({
      heading: {
        levels: [1, 2, 3],
      },
    }),
    Link.configure({
      openOnClick: false,
      HTMLAttributes: {
        target: '_blank',
        rel: 'noopener noreferrer',
      },
    }),
    Image.configure({
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

const addImage = () => {
  const url = window.prompt('Image URL');

  if (url) {
    editor.value.chain().focus().setImage({ src: url }).run();
  }
};

const addYoutubeVideo = () => {
  const url = window.prompt('YouTube URL');

  if (url) {
    editor.value.chain().focus().setYoutubeVideo({ src: url }).run();
  }
};
</script>

<style scoped>
.toolbar-btn {
  @apply px-2 py-1 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-neutral-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors;
}

.toolbar-btn.is-active {
  @apply bg-gray-300 dark:bg-neutral-600;
}

:deep(.ProseMirror) {
  min-height: 300px;
  outline: none;
}

:deep(.ProseMirror p.is-editor-empty:first-child::before) {
  content: attr(data-placeholder);
  float: left;
  color: #adb5bd;
  pointer-events: none;
  height: 0;
}

:deep(.ProseMirror) {
  @apply dark:text-neutral-200;
}

:deep(.ProseMirror h1) {
  @apply text-3xl font-bold mt-6 mb-4;
}

:deep(.ProseMirror h2) {
  @apply text-2xl font-bold mt-5 mb-3;
}

:deep(.ProseMirror h3) {
  @apply text-xl font-bold mt-4 mb-2;
}

:deep(.ProseMirror ul) {
  @apply list-disc ml-6 my-4;
}

:deep(.ProseMirror ol) {
  @apply list-decimal ml-6 my-4;
}

:deep(.ProseMirror blockquote) {
  @apply border-l-4 border-gray-300 dark:border-neutral-600 pl-4 italic my-4;
}

:deep(.ProseMirror a) {
  @apply text-blue-600 dark:text-blue-400 underline;
}

:deep(.ProseMirror code) {
  @apply bg-gray-100 dark:bg-neutral-800 px-1 py-0.5 rounded text-sm;
}
</style>
