<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Modal panel -->
            <div
                class="inline-block bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg w-full">
                <form @submit.prevent="handleSubmit">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4"
                                    id="modal-title">
                                    {{ editing ? 'Edit Category' : 'Add New Category' }}
                                </h3>

                                <div class="space-y-4">
                                    <!-- Type Selection with Radio Buttons -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Category Type
                                        </label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center cursor-pointer">
                                                <input type="radio" v-model="form.type" value="setlist" :disabled="editing"
                                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:border-gray-600 dark:focus:ring-blue-600">
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Setlist</span>
                                            </label>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="radio" v-model="form.type" value="unit_song" :disabled="editing"
                                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:border-gray-600 dark:focus:ring-blue-600">
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Unit Song</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Setlist Dropdown (only for Unit Song) -->
                                    <div v-if="form.type === 'unit_song'">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Setlist <span class="text-red-500">*</span>
                                        </label>
                                        <select v-model="form.setlist_id" required
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">Select Setlist</option>
                                            <option v-for="setlist in setlists" :key="setlist.id" :value="setlist.id">
                                                {{ setlist.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Name Field -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ form.type === 'setlist' ? 'Setlist Name' : 'Unit Song Name' }} <span class="text-red-500">*</span>
                                        </label>
                                        <input v-model="form.name" type="text" required
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :placeholder="form.type === 'setlist' ? 'Enter setlist name' : 'Enter unit song name'" />
                                    </div>

                                    <!-- JP Name Field -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Nama (Bahasa Jepang)
                                        </label>
                                        <input v-model="form.jp_name" type="text"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :placeholder="form.type === 'setlist' ? 'Enter setlist name in Japanese' : 'Enter unit song name in Japanese'" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ editing ? 'Update' : 'Save' }}
                        </button>
                        <button type="button" @click="$emit('close')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    category: Object,
    setlists: Array,
});

const emit = defineEmits(['close']);

const form = ref({
    type: 'setlist',
    name: '',
    jp_name: '',
    setlist_id: '',
});

const editing = ref(false);

watch(() => props.category, (newCategory) => {
    if (newCategory) {
        editing.value = true;
        form.value = {
            type: newCategory.type,
            name: newCategory.name,
            jp_name: newCategory.jp_name || '',
            setlist_id: newCategory.setlist_id || '',
        };
    } else {
        editing.value = false;
        form.value = {
            type: 'setlist',
            name: '',
            jp_name: '',
            setlist_id: '',
        };
    }
}, { immediate: true });

// Reset setlist_id when switching to setlist type
watch(() => form.value.type, (newType) => {
    if (newType === 'setlist') {
        form.value.setlist_id = '';
    }
});

const handleSubmit = () => {
    if (editing.value) {
        router.put(route('show-teater.categories.update', props.category.id), form.value, {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
            },
        });
    } else {
        router.post(route('show-teater.categories.store'), form.value, {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
                form.value = { type: 'setlist', name: '', jp_name: '', setlist_id: '' };
            },
        });
    }
};
</script>
