<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';

const props = defineProps({ settings: Object })

import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage()
const latestAppSettings = computed(() => page.props.appSettings || props.settings || {})

const form = useForm({
  app_name: latestAppSettings.value.app_name || '',
  sidebar_name: latestAppSettings.value.sidebar_name || '',
  desc_app: latestAppSettings.value.desc_app || '',
  app_logo: null,
  hero_image: null,
  login_image: null,
  showroom_room_id: latestAppSettings.value.showroom_room_id || '416491',
  showroom_link: latestAppSettings.value.showroom_link || 'https://www.showroom-live.com/r/48_KOKOHA_EGUCHI',
  instagram_url: latestAppSettings.value.instagram_url || '',
  twitter_url: latestAppSettings.value.twitter_url || '',
  tiktok_url: latestAppSettings.value.tiktok_url || '',
  hero_button_1_text: latestAppSettings.value.hero_button_1_text || 'Info Lebih Lanjut',
  hero_button_1_link: latestAppSettings.value.hero_button_1_link || '/blog',
  hero_button_2_text: latestAppSettings.value.hero_button_2_text || 'Temukan Kami',
  hero_button_2_link: latestAppSettings.value.hero_button_2_link || '/blog',
  show_youtube_playlist: latestAppSettings.value.show_youtube_playlist || 'false',
  youtube_playlist_url: latestAppSettings.value.youtube_playlist_url || '',
  show_gallery_carousel: latestAppSettings.value.show_gallery_carousel || 'true',
})

const confirmingSave = ref(false)

// Image preview URLs
const appLogoPreview = ref(latestAppSettings.value.app_logo || '')
const heroImagePreview = ref(latestAppSettings.value.hero_image || '')
const loginImagePreview = ref(latestAppSettings.value.login_image || '')

const handleImageUpload = (field, event) => {
  const file = event.target.files[0]
  if (!file) return

  form[field] = file

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    if (field === 'app_logo') appLogoPreview.value = e.target.result
    else if (field === 'hero_image') heroImagePreview.value = e.target.result
    else if (field === 'login_image') loginImagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const hasChanges = computed(() => {
  const s = latestAppSettings.value || {}
  if (form.app_name !== (s.app_name || '')) return true
  if (form.sidebar_name !== (s.sidebar_name || '')) return true
  if (form.desc_app !== (s.desc_app || '')) return true
  if (form.showroom_room_id !== (s.showroom_room_id || '416491')) return true
  if (form.showroom_link !== (s.showroom_link || 'https://www.showroom-live.com/r/JKT48_Greesel')) return true
  if (form.instagram_url !== (s.instagram_url || '')) return true
  if (form.twitter_url !== (s.twitter_url || '')) return true
  if (form.tiktok_url !== (s.tiktok_url || '')) return true
  if (form.hero_button_1_text !== (s.hero_button_1_text || 'Info Lebih Lanjut')) return true
  if (form.hero_button_1_link !== (s.hero_button_1_link || '/blog')) return true
  if (form.hero_button_2_text !== (s.hero_button_2_text || 'Temukan Kami')) return true
  if (form.hero_button_2_link !== (s.hero_button_2_link || '/blog')) return true
  if (form.show_youtube_playlist !== (s.show_youtube_playlist || 'false')) return true
  if (form.youtube_playlist_url !== (s.youtube_playlist_url || '')) return true
  if (form.show_gallery_carousel !== (s.show_gallery_carousel || 'true')) return true
  if (form.app_logo || form.hero_image || form.login_image) return true
  return false
})

const syncFormFromServer = (incoming) => {
  const server = incoming || latestAppSettings.value || {}
  form.app_name = server.app_name ?? form.app_name ?? ''
  form.sidebar_name = server.sidebar_name ?? ''
  form.desc_app = server.desc_app ?? ''
  form.showroom_room_id = server.showroom_room_id ?? '416491'
  form.showroom_link = server.showroom_link ?? 'https://www.showroom-live.com/r/JKT48_Greesel'
  form.instagram_url = server.instagram_url ?? ''
  form.twitter_url = server.twitter_url ?? ''
  form.tiktok_url = server.tiktok_url ?? ''
  form.hero_button_1_text = server.hero_button_1_text ?? 'Info Lebih Lanjut'
  form.hero_button_1_link = server.hero_button_1_link ?? '/blog'
  form.hero_button_2_text = server.hero_button_2_text ?? 'Temukan Kami'
  form.hero_button_2_link = server.hero_button_2_link ?? '/blog'
  form.show_youtube_playlist = server.show_youtube_playlist ?? 'false'
  form.youtube_playlist_url = server.youtube_playlist_url ?? ''
  form.show_gallery_carousel = server.show_gallery_carousel ?? 'true'
  form.app_logo = null
  form.hero_image = null
  form.login_image = null
  appLogoPreview.value = server.app_logo || ''
  heroImagePreview.value = server.hero_image || ''
  loginImagePreview.value = server.login_image || ''
}

watch(() => page.props.appSettings, (server) => {
  if (!server) return
  syncFormFromServer(server)
}, { deep: true })

const submit = () => {
  confirmingSave.value = true
}

const confirmSave = () => {
  confirmingSave.value = false
  form.post(route('settings.update'), {
    forceFormData: true,
    onSuccess: (page) => syncFormFromServer(page?.props?.appSettings)
  })
}
</script>

<template>
  <AppLayout title="Settings">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Settings
      </h2>
    </template>

    <form @submit.prevent="submit" class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-6">
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg border ring-1 ring-white/5">
        <div class="mb-4">
          <div class="text-lg font-medium text-gray-900 dark:text-gray-200">Application Name</div>
          <p class="text-sm text-gray-500 dark:text-gray-400">Update your Application Name</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">App Name</label>
          <input v-model="form.app_name" type="text"
            class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
        </div>

        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sidebar / Short Name</label>
          <p class="text-xs text-gray-400 mb-2">Short name used in the sidebar and browser tab (example: "Fansite")</p>
          <input v-model="form.sidebar_name" type="text" maxlength="64"
            class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
        </div>

        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">App Description</label>
          <p class="text-xs text-gray-400 mb-2">Short description displayed on the hero section (max 500 characters)</p>
          <textarea v-model="form.desc_app" maxlength="500" rows="3"
            class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white"></textarea>
        </div>
      </div>

      <!-- Visual Settings Section -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg border ring-1 ring-white/5">
        <div class="mb-4">
          <div class="text-lg font-medium text-gray-900 dark:text-gray-200">Visual Settings</div>
          <p class="text-sm text-gray-500 dark:text-gray-400">Upload and preview images</p>
        </div>

        <!-- App Logo -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">App Logo</label>
          <div class="flex items-start gap-4">
            <div v-if="appLogoPreview" class="shrink-0">
              <img :src="appLogoPreview" alt="App Logo Preview"
                class="h-20 w-auto object-contain border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-800" />
            </div>
            <div class="flex-1">
              <input type="file" accept="image/*" @change="handleImageUpload('app_logo', $event)"
                class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300" />
              <p class="text-xs text-gray-400 mt-1">Max 2MB. PNG, JPG, SVG recommended.</p>
            </div>
          </div>
        </div>

        <!-- Hero Image -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hero Image</label>
          <div class="flex items-start gap-4">
            <div v-if="heroImagePreview" class="shrink-0">
              <img :src="heroImagePreview" alt="Hero Image Preview"
                class="h-32 w-auto object-cover border border-gray-300 dark:border-gray-600 rounded" />
            </div>
            <div class="flex-1">
              <input type="file" accept="image/*" @change="handleImageUpload('hero_image', $event)"
                class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300" />
              <p class="text-xs text-gray-400 mt-1">Max 5MB. Displayed on the welcome page.</p>
            </div>
          </div>
        </div>

        <!-- Login Image -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Login Panel Image</label>
          <div class="flex items-start gap-4">
            <div v-if="loginImagePreview" class="shrink-0">
              <img :src="loginImagePreview" alt="Login Image Preview"
                class="h-32 w-auto object-cover border border-gray-300 dark:border-gray-600 rounded" />
            </div>
            <div class="flex-1">
              <input type="file" accept="image/*" @change="handleImageUpload('login_image', $event)"
                class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300" />
              <p class="text-xs text-gray-400 mt-1">Max 5MB. Displayed on login/register pages.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Showroom Settings Section -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg border ring-1 ring-white/5">
        <div class="mb-4">
          <div class="text-lg font-medium text-gray-900 dark:text-gray-200">Showroom Live Settings</div>
          <p class="text-sm text-gray-500 dark:text-gray-400">Configure Showroom Live integration for your homepage</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Room ID</label>
          <p class="text-xs text-gray-400 mb-2">The Showroom room ID to check live status (e.g., 416491)</p>
          <input v-model="form.showroom_room_id" type="text"
            class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
        </div>

        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Showroom Link</label>
          <p class="text-xs text-gray-400 mb-2">Full URL to redirect when live (e.g., https://www.showroom-live.com/r/JKT48_Greesel)</p>
          <input v-model="form.showroom_link" type="url"
            class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
        </div>
      </div>

      <!-- Social Media Settings Section -->
      <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg border ring-1 ring-white/5">
        <div class="mb-4">
          <div class="text-lg font-medium text-gray-900 dark:text-gray-200">Social Media Links</div>
          <p class="text-sm text-gray-500 dark:text-gray-400">Add your social media profile URLs to display icons in the header</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Instagram URL</label>
          <p class="text-xs text-gray-400 mb-2">Full Instagram profile URL (e.g., https://instagram.com/yourprofile)</p>
          <input v-model="form.instagram_url" type="url"
            class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            placeholder="https://instagram.com/yourprofile" />
        </div>

        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Twitter/X URL</label>
          <p class="text-xs text-gray-400 mb-2">Full Twitter/X profile URL (e.g., https://twitter.com/yourprofile)</p>
          <input v-model="form.twitter_url" type="url"
            class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            placeholder="https://twitter.com/yourprofile" />
        </div>

        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">TikTok URL</label>
          <p class="text-xs text-gray-400 mb-2">Full TikTok profile URL (e.g., https://tiktok.com/@yourprofile)</p>
          <input v-model="form.tiktok_url" type="url"
            class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            placeholder="https://tiktok.com/@yourprofile" />
        </div>

        <!-- Hero Button Settings -->
        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Hero Section Buttons</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Button 1 Text</label>
              <p class="text-xs text-gray-400 mb-2">Text for the first button in hero section</p>
              <input v-model="form.hero_button_1_text" type="text"
                class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                placeholder="Info Lebih Lanjut" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Button 1 Link</label>
              <p class="text-xs text-gray-400 mb-2">URL or route path (e.g., /blog or https://example.com)</p>
              <input v-model="form.hero_button_1_link" type="text"
                class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                placeholder="/blog" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Button 2 Text</label>
              <p class="text-xs text-gray-400 mb-2">Text for the second button in hero section</p>
              <input v-model="form.hero_button_2_text" type="text"
                class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                placeholder="Temukan Kami" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Button 2 Link</label>
              <p class="text-xs text-gray-400 mb-2">URL or route path (e.g., /blog or https://example.com)</p>
              <input v-model="form.hero_button_2_link" type="text"
                class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                placeholder="/blog" />
            </div>
          </div>
        </div>

        <!-- YouTube Playlist Settings -->
        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">YouTube Playlist</h3>

          <div class="mb-4">
            <label class="flex items-center">
              <input
                type="checkbox"
                :checked="form.show_youtube_playlist === 'true'"
                @change="form.show_youtube_playlist = $event.target.checked ? 'true' : 'false'"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-700"
              />
              <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show YouTube Playlist on Homepage</span>
            </label>
            <p class="text-xs text-gray-400 mt-1 ml-6">Enable this to display a YouTube playlist embed on the homepage</p>
          </div>

          <div v-if="form.show_youtube_playlist === 'true'" class="mt-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">YouTube Playlist URL</label>
            <p class="text-xs text-gray-400 mb-2">Full YouTube playlist URL (e.g., https://www.youtube.com/playlist?list=...)</p>
            <input
              v-model="form.youtube_playlist_url"
              type="url"
              class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
              placeholder="https://www.youtube.com/playlist?list=PLxxxxxxxxxxxxx"
            />
          </div>
        </div>

        <!-- Gallery Carousel Settings -->
        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Gallery Carousel</h3>

          <div class="mb-4">
            <label class="flex items-center">
              <input
                type="checkbox"
                :checked="form.show_gallery_carousel === 'true'"
                @change="form.show_gallery_carousel = $event.target.checked ? 'true' : 'false'"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-700"
              />
              <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show Gallery Carousel on Homepage</span>
            </label>
            <p class="text-xs text-gray-400 mt-1 ml-6">Enable this to display 5 latest gallery photos in a carousel on the homepage</p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <PrimaryButton type="submit" class="px-4 py-2 bg-brand-500 text-white rounded" :disabled="form.processing">
          Save
        </PrimaryButton>
        <span v-if="$page.props.flash?.success" class="text-green-600">{{ $page.props.flash?.success }}</span>
      </div>
    </form>

    <!-- Confirmation modal before save -->
    <DialogModal :show="confirmingSave" @close="confirmingSave = false">
      <template #title>Confirm changes</template>
      <template #content>
        <p class="text-sm text-gray-600 dark:text-gray-400">You're about to save changes to site settings. This will
          update the app name, sidebar name, and any uploaded images. Continue?</p>
      </template>
      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <button type="button" @click="confirmingSave = false"
            class="px-3 py-2 border rounded text-sm dark:border-gray-600 dark:text-gray-300">Cancel</button>
          <button type="button" @click="confirmSave"
            class="px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Confirm Save</button>
        </div>
      </template>
    </DialogModal>
  </AppLayout>
</template>
