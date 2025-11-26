<script setup>
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({ settings: Object })

import { computed } from 'vue'
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
  app_name: props.settings.app_name || '',
  sidebar_name: props.settings.sidebar_name || '',
    logo: null,
    hero_image: null,
    login_image: null,
    // removal flags
    remove_logo: false,
    remove_hero_image: false,
    remove_login_image: false,
})

const preview = ref({
    logo: props.settings.logo || null,
    hero: props.settings.hero_image || null,
    login: props.settings.login_image || null,
})

const confirmingRemove = ref(null) // name of field being removed (logo, hero_image, login_image, sidebar_name)
const confirmingSave = ref(false)

const onFileChange = (field, e) => {
    const file = e.target.files[0]
    // choosing a new file clears any previous remove flag for that field
    form.set(field, file)
    const removeKey = 'remove_' + field
    form.set(removeKey, false)
    if (!file) return

    // show local preview
    const reader = new FileReader()
    reader.onload = (ev) => preview.value[field === 'logo' ? 'logo' : field === 'hero_image' ? 'hero' : 'login'] = ev.target.result
    reader.readAsDataURL(file)
}

const requestRemove = (field) => {
  confirmingRemove.value = field
}

const confirmRemove = () => {
  const field = confirmingRemove.value
  if (!field) return

  if (field === 'sidebar_name') {
    form.sidebar_name = ''
    form.set('remove_sidebar_name', true)
  } else {
    // clear client-selected file and signal removal
    form.set(field, null)
    form.set('remove_' + field, true)

    // clear preview
    if (field === 'logo') preview.value.logo = null
    if (field === 'hero_image') preview.value.hero = null
    if (field === 'login_image') preview.value.login = null
  }

  confirmingRemove.value = null
}

const cancelRemove = () => confirmingRemove.value = null

const hasChanges = computed(() => {
    const s = props.settings || {}
    if (form.app_name !== (s.app_name || '')) return true
  if (form.sidebar_name !== (s.sidebar_name || '')) return true
    if (form.logo) return true
    if (form.hero_image) return true
    if (form.login_image) return true
    if (form.remove_logo) return true
    if (form.remove_hero_image) return true
    if (form.remove_login_image) return true
  if (form.remove_sidebar_name) return true
    return false
})

const submit = () => {
    // use form.put to ensure the request method matches the PUT route
  // If significant changes are present (sidebar_name changed or any removals), ask for confirmation
  const s = props.settings || {}
  const sidebarChanged = form.sidebar_name !== (s.sidebar_name || '')
  const willRemove = form.remove_logo || form.remove_hero_image || form.remove_login_image || form.remove_sidebar_name

  if (sidebarChanged || willRemove) {
    confirmingSave.value = true
    return
  }

    form.put(route('settings.update'), {
        onSuccess: (page) => {
            // after server successfully stores files, update previews to the stored URLs
            const server = page.props.appSettings || {}
            preview.value.logo = server.logo ?? preview.value.logo
            preview.value.hero = server.hero_image ?? preview.value.hero
            preview.value.login = server.login_image ?? preview.value.login
            // update text fields from server if present
            if (server.app_name) form.app_name = server.app_name
            if (server.sidebar_name) form.sidebar_name = server.sidebar_name
        }
    })
}

const confirmSave = () => {
  confirmingSave.value = false
  form.put(route('settings.update'), {
    onSuccess: (page) => {
      const server = page.props.appSettings || {}
      preview.value.logo = server.logo ?? preview.value.logo
      preview.value.hero = server.hero_image ?? preview.value.hero
      preview.value.login = server.login_image ?? preview.value.login
      if (server.app_name) form.app_name = server.app_name
      if (server.sidebar_name) form.sidebar_name = server.sidebar_name
    }
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
              <label class="block text-sm font-medium text-gray-700">App Name</label>
              <input v-model="form.app_name" type="text" class="mt-1 block w-full rounded border-gray-300" />
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700">Sidebar / Short Name</label>
              <p class="text-xs text-gray-400 mb-2">Short name used in the sidebar and browser tab (example: "Fansite")</p>
              <div class="flex items-center gap-2">
                <input v-model="form.sidebar_name" type="text" maxlength="64" class="mt-1 block w-full rounded border-gray-300" />
                <button v-if="(props.settings && props.settings.sidebar_name) || form.sidebar_name" type="button" @click.prevent="requestRemove('sidebar_name')" class="mt-1 inline-flex items-center px-2 py-1 text-xs border rounded text-gray-600 dark:text-gray-300">Remove</button>
              </div>
            </div>
          </div>

          <!-- Images: logo / hero / login -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
            <div class="bg-white dark:bg-zinc-900 p-4 rounded-lg border ring-1 ring-white/5">
              <label class="block text-sm font-medium text-gray-700">Application Logo (PNG / SVG)</label>
              <input type="file" accept="image/*" @change="e => onFileChange('logo', e)" class="mt-2 block w-full" />

              <div v-if="preview.logo" class="mt-2 w-32 h-12 overflow-hidden rounded border relative">
                <img :src="preview.logo" class="w-full h-full object-contain" />
                <button type="button" @click.prevent="requestRemove('logo')" class="absolute top-1 end-1 bg-white/60 rounded px-2 py-1 text-xs">Remove</button>
              </div>
              <div v-else class="mt-2 text-sm text-gray-500">No logo set</div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-lg border ring-1 ring-white/5">
              <label class="block text-sm font-medium text-gray-700">Hero Image (PNG / JPG / SVG)</label>
              <input type="file" accept="image/*" @change="e => onFileChange('hero_image', e)" class="mt-2 block w-full" />

              <div v-if="preview.hero" class="mt-2 w-full h-24 overflow-hidden rounded border relative">
                <img :src="preview.hero" class="w-full h-full object-cover" />
                <button type="button" @click.prevent="requestRemove('hero_image')" class="absolute top-1 end-1 bg-white/60 rounded px-2 py-1 text-xs">Remove</button>
              </div>
              <div v-else class="mt-2 text-sm text-gray-500">No hero image set</div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-lg border ring-1 ring-white/5">
              <label class="block text-sm font-medium text-gray-700">Login Panel Image (PNG / JPG / SVG)</label>
              <input type="file" accept="image/*" @change="e => onFileChange('login_image', e)" class="mt-2 block w-full" />

              <div v-if="preview.login" class="mt-2 w-full h-24 overflow-hidden rounded border relative">
                <img :src="preview.login" class="w-full h-full object-cover" />
                <button type="button" @click.prevent="requestRemove('login_image')" class="absolute top-1 end-1 bg-white/60 rounded px-2 py-1 text-xs">Remove</button>
              </div>
              <div v-else class="mt-2 text-sm text-gray-500">No login image set</div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <PrimaryButton type="submit" class="px-4 py-2 bg-brand-500 text-white rounded" :disabled="form.processing">Save</PrimaryButton>
            <span v-if="$page.props.flash?.success" class="text-green-600">{{$page.props.flash?.success}}</span>
          </div>
        </form>

        <!-- Confirmation modal for field removal (logo/hero/login/sidebar_name) -->
        <ConfirmationModal :show="!!confirmingRemove" @close="cancelRemove">
          <template #title>
            Confirm remove
          </template>

          <template #content>
            <div v-if="confirmingRemove === 'sidebar_name'">
              You're about to remove the sidebar short name. This will clear the value shown in the sidebar and in browser tabs. Are you sure?
            </div>
            <div v-else-if="confirmingRemove === 'logo'">
              This will remove the stored application logo. The site will fall back to the default mark. Continue?
            </div>
            <div v-else-if="confirmingRemove === 'hero_image'">
              This will remove the saved hero image used in the Welcome page. Continue?
            </div>
            <div v-else>
              This will remove the saved login panel image. Continue?
            </div>
          </template>

          <template #footer>
            <div class="flex items-center justify-end gap-2">
              <button type="button" @click="cancelRemove" class="px-3 py-2 border rounded text-sm">Cancel</button>
              <button type="button" @click="confirmRemove" class="px-3 py-2 bg-red-600 text-white rounded text-sm">Remove</button>
            </div>
          </template>
        </ConfirmationModal>

        <!-- Confirmation modal before save when sidebar_name or removals are detected -->
        <DialogModal :show="confirmingSave" @close="confirmingSave = false">
          <template #title>Confirm changes</template>
          <template #content>
            <p class="text-sm text-gray-600 dark:text-gray-400">You're about to apply changes to site settings. These may include clearing images or the sidebar short name. Continue?</p>
          </template>
          <template #footer>
            <div class="flex items-center justify-end gap-2">
              <button type="button" @click="confirmingSave = false" class="px-3 py-2 border rounded text-sm">Cancel</button>
              <button type="button" @click="confirmSave" class="px-3 py-2 bg-brand-500 text-white rounded text-sm">Confirm</button>
            </div>
          </template>
        </DialogModal>
    </AppLayout>
</template>
