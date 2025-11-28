<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({ settings: Object })

import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage()
const latestAppSettings = computed(() => page.props.appSettings || props.settings || {})

const form = useForm({
  app_name: latestAppSettings.value.app_name || '',
  sidebar_name: latestAppSettings.value.sidebar_name || '',
  remove_sidebar_name: false,
})

const confirmingRemove = ref(null) // currently only used for sidebar_name
const confirmingSave = ref(false)

const requestRemove = (field) => {
  confirmingRemove.value = field
}

const confirmRemove = () => {
  const field = confirmingRemove.value
  if (!field) return

  if (field === 'sidebar_name') {
    form.sidebar_name = ''
    form.set('remove_sidebar_name', true)
  }

  confirmingRemove.value = null
}

const cancelRemove = () => confirmingRemove.value = null

const hasChanges = computed(() => {
    const s = latestAppSettings.value || {}
    if (form.app_name !== (s.app_name || '')) return true
  if (form.sidebar_name !== (s.sidebar_name || '')) return true
  if (form.remove_sidebar_name) return true
    return false
})

const syncFormFromServer = (incoming) => {
    const server = incoming || latestAppSettings.value || {}
    form.app_name = server.app_name ?? form.app_name ?? ''
    form.sidebar_name = server.sidebar_name ?? ''
    form.set('remove_sidebar_name', false)
}

watch(() => page.props.appSettings, (server) => {
    if (!server) return
    syncFormFromServer(server)
}, { deep: true })

const submit = () => {
  const s = latestAppSettings.value || {}
  const sidebarChanged = form.sidebar_name !== (s.sidebar_name || '')
  const willRemove = form.remove_sidebar_name

  if (sidebarChanged || willRemove) {
    confirmingSave.value = true
    return
  }

  form.put(route('settings.update'), {
    onSuccess: (page) => {
      syncFormFromServer(page?.props?.appSettings)
    }
  })
}

const confirmSave = () => {
  confirmingSave.value = false
  form.put(route('settings.update'), {
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

          <div class="flex items-center gap-3">
            <PrimaryButton type="submit" class="px-4 py-2 bg-brand-500 text-white rounded" :disabled="form.processing">Save</PrimaryButton>
            <span v-if="$page.props.flash?.success" class="text-green-600">{{$page.props.flash?.success}}</span>
          </div>
        </form>

        <!-- Confirmation modal for sidebar_name removal -->
        <ConfirmationModal :show="!!confirmingRemove" @close="cancelRemove">
          <template #title>
            Confirm remove
          </template>

          <template #content>
            You're about to remove the sidebar short name. This will clear the value shown in the sidebar and in browser tabs. Are you sure?
          </template>

          <template #footer>
            <div class="flex items-center justify-end gap-2">
              <button type="button" @click="cancelRemove" class="px-3 py-2 border rounded text-sm">Cancel</button>
              <button type="button" @click="confirmRemove" class="bg-red px-3 py-2 bg-red-600 text-white rounded text-sm">Remove</button>
            </div>
          </template>
        </ConfirmationModal>

        <!-- Confirmation modal before save when sidebar_name or removals are detected -->
        <DialogModal :show="confirmingSave" @close="confirmingSave = false">
          <template #title>Confirm changes</template>
          <template #content>
            <p class="text-sm text-gray-600 dark:text-gray-400">You're about to apply changes to site settings, including clearing the sidebar short name when requested. Continue?</p>
          </template>
          <template #footer>
            <div class="flex items-center justify-end gap-2">
              <button type="button" @click="confirmingSave = false" class="px-3 py-2 border rounded text-sm">Cancel</button>
              <button type="button" @click="confirmSave" class="px-3 py-2 bg-gray-500 text-white rounded text-sm">Confirm</button>
            </div>
          </template>
        </DialogModal>
    </AppLayout>
</template>
