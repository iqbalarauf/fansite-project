<template>
  <Head title="Create Page" />

  <AppLayout title="Create Page">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Create New Page
      </h2>
    </template>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Title -->
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Page Title</label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          />
          <div v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</div>
        </div>

        <!-- Hero Image Upload -->
        <div>
          <label for="hero_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hero Image (16:9 ratio recommended)</label>
          <input
            id="hero_image"
            type="file"
            accept="image/*"
            @change="handleHeroImageUpload"
            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300"
          />
          <div v-if="form.errors.hero_image" class="text-sm text-red-600 mt-1">{{ form.errors.hero_image }}</div>
          
          <!-- Hero Image Preview with 16:9 aspect ratio -->
          <div v-if="heroImagePreview" class="mt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Preview:</p>
            <div class="w-full relative">
              <div class="w-full pb-[56.25%] relative bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden">
                <img :src="heroImagePreview" alt="Hero preview" class="absolute inset-0 w-full h-full object-cover" />
              </div>
            </div>
          </div>
        </div>

        <!-- Body Content -->
        <div>
          <label for="body" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Page Content</label>
          <div class="mt-1">
            <TiptapEditor v-model="form.body" />
          </div>
          <div v-if="form.errors.body" class="text-sm text-red-600 mt-1">{{ form.errors.body }}</div>
        </div>

        <!-- CTA Section Checkbox -->
        <div class="flex items-center">
          <input
            id="has_cta_section"
            v-model="form.has_cta_section"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
          />
          <label for="has_cta_section" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
            Add Call-to-Action Section
          </label>
        </div>

        <!-- CTA Section Fields (conditional) -->
        <div v-if="form.has_cta_section" class="space-y-4 p-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Call-to-Action Section</h3>
          
          <!-- CTA Background Image -->
          <div>
            <label for="cta_bg_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Background Image</label>
            <input
              id="cta_bg_image"
              type="file"
              accept="image/*"
              @change="handleCtaBgImageUpload"
              class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300"
            />
            <div v-if="form.errors.cta_bg_image" class="text-sm text-red-600 mt-1">{{ form.errors.cta_bg_image }}</div>
            
            <!-- CTA Background Preview -->
            <div v-if="ctaBgImagePreview" class="mt-2">
              <img :src="ctaBgImagePreview" alt="CTA background preview" class="w-full h-48 object-cover rounded-lg" />
            </div>
          </div>

          <!-- CTA Title -->
          <div>
            <label for="cta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
            <input
              id="cta_title"
              v-model="form.cta_title"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <div v-if="form.errors.cta_title" class="text-sm text-red-600 mt-1">{{ form.errors.cta_title }}</div>
          </div>

          <!-- CTA Description -->
          <div>
            <label for="cta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea
              id="cta_description"
              v-model="form.cta_description"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            ></textarea>
            <div v-if="form.errors.cta_description" class="text-sm text-red-600 mt-1">{{ form.errors.cta_description }}</div>
          </div>

          <!-- CTA Button Text -->
          <div>
            <label for="cta_button_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Button Text</label>
            <input
              id="cta_button_text"
              v-model="form.cta_button_text"
              type="text"
              placeholder="e.g., Learn More"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <div v-if="form.errors.cta_button_text" class="text-sm text-red-600 mt-1">{{ form.errors.cta_button_text }}</div>
          </div>

          <!-- CTA Button URL -->
          <div>
            <label for="cta_button_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Button URL</label>
            <input
              id="cta_button_url"
              v-model="form.cta_button_url"
              type="url"
              placeholder="https://example.com"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <div v-if="form.errors.cta_button_url" class="text-sm text-red-600 mt-1">{{ form.errors.cta_button_url }}</div>
          </div>
        </div>

        <!-- Status -->
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
          <select
            id="status"
            v-model="form.status"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          >
            <option value="draft">Draft</option>
            <option value="published">Published</option>
          </select>
          <div v-if="form.errors.status" class="text-sm text-red-600 mt-1">{{ form.errors.status }}</div>
        </div>

        <!-- Menu Settings -->
        <div class="space-y-4 p-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Menu Settings</h3>
          
          <!-- Show in Menu Checkbox -->
          <div class="flex items-center">
            <input
              id="show_in_menu"
              v-model="form.show_in_menu"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
            />
            <label for="show_in_menu" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
              Show this page in the public navigation menu
            </label>
          </div>

          <!-- Menu Order -->
          <div v-if="form.show_in_menu">
            <label for="menu_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Menu Order (lower numbers appear first)</label>
            <input
              id="menu_order"
              v-model.number="form.menu_order"
              type="number"
              min="0"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Note: Blog and About are permanent menu items</p>
            <div v-if="form.errors.menu_order" class="text-sm text-red-600 mt-1">{{ form.errors.menu_order }}</div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
          <Link :href="route('pages.index')" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
            :class="{ 'opacity-25': form.processing }"
          >
            Create Page
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue';

const form = useForm({
  title: '',
  hero_image: null,
  body: '',
  has_cta_section: false,
  cta_bg_image: null,
  cta_title: '',
  cta_description: '',
  cta_button_text: '',
  cta_button_url: '',
  status: 'draft',
  show_in_menu: false,
  menu_order: 0,
});

const heroImagePreview = ref(null);
const ctaBgImagePreview = ref(null);

const handleHeroImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.hero_image = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      heroImagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handleCtaBgImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.cta_bg_image = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      ctaBgImagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const submit = () => {
  form.post(route('pages.store'), {
    forceFormData: true,
  });
};
</script>
