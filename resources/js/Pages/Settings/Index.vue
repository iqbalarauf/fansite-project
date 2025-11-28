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
  if (form.app_logo || form.hero_image || form.login_image) return true
  return false
})

const syncFormFromServer = (incoming) => {
  const server = incoming || latestAppSettings.value || {}
  form.app_name = server.app_name ?? form.app_name ?? ''
  form.sidebar_name = server.sidebar_name ?? ''
  form.desc_app = server.desc_app ?? ''
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