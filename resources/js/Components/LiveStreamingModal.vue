<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    show: Boolean,
    editingStream: Object,
});

const emit = defineEmits(['close']);

const form = useForm({
    platform: 'Showroom',
    live_date: '',
    duration: '',
    additional_info: '',
});

const deleteForm = useForm({});
const showDeleteConfirm = ref(false);

// Helper function to format date for input[type="date"]
const formatDateForInput = (dateString) => {
    if (!dateString) return '';

    // If already in YYYY-MM-DD format
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
        return dateString;
    }

    // Try to parse and format
    try {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    } catch (e) {
        return '';
    }
};

watch(() => props.show, (newValue) => {
    if (newValue) {
        if (props.editingStream) {
            form.platform = props.editingStream.platform;
            form.live_date = formatDateForInput(props.editingStream.live_date);
            form.duration = props.editingStream.duration !== null ? String(props.editingStream.duration) : '';
            form.additional_info = props.editingStream.additional_info || '';
        } else {
            form.reset();
            form.platform = 'Showroom';
            form.live_date = '';
            form.duration = '';
            form.additional_info = '';
        }
        showDeleteConfirm.value = false;
    }
});

const submit = () => {
    if (props.editingStream) {
        form.put(route('live-streaming.update', props.editingStream.id), {
            onSuccess: () => {
                emit('close');
                form.reset();
            },
        });
    } else {
        form.post(route('live-streaming.store'), {
            onSuccess: () => {
                emit('close');
                form.reset();
            },
        });
    }
};

const confirmDelete = () => {
    showDeleteConfirm.value = true;
};

const deleteStream = () => {
    deleteForm.delete(route('live-streaming.destroy', props.editingStream.id), {
        onSuccess: () => {
            emit('close');
            showDeleteConfirm.value = false;
        },
    });
};

const closeModal = () => {
    if (!form.processing && !deleteForm.processing) {
        emit('close');
        form.reset();
        showDeleteConfirm.value = false;
    }
};
</script>

<template>
    <div v-if="show" class="fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div v-if="!showDeleteConfirm" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="submit">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                            {{ editingStream ? `Edit Live Stream #${editingStream.id}` : 'Add Live Stream' }}
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <InputLabel for="platform" value="Platform *" />
                                <select
                                    id="platform"
                                    v-model="form.platform"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="Showroom">Showroom</option>
                                    <option value="IDN App">IDN App</option>
                                </select>
                                <p v-if="form.errors.platform" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.platform }}</p>
                            </div>

                            <div>
                                <InputLabel for="live_date" value="Live Date *" />
                                <TextInput
                                    id="live_date"
                                    v-model="form.live_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <p v-if="form.errors.live_date" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.live_date }}</p>
                            </div>

                            <div>
                                <InputLabel for="duration" value="Duration (minutes)" />
                                <TextInput
                                    id="duration"
                                    v-model="form.duration"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                    placeholder="Optional"
                                />
                                <p v-if="form.errors.duration" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.duration }}</p>
                            </div>

                            <div>
                                <InputLabel for="additional_info" value="Additional Info" />
                                <textarea
                                    id="additional_info"
                                    v-model="form.additional_info"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    placeholder="Optional notes"
                                ></textarea>
                                <p v-if="form.errors.additional_info" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.additional_info }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" :disabled="form.processing"
                            class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : (editingStream ? 'Update' : 'Create') }}
                        </button>
                        <button v-if="editingStream" type="button" @click="confirmDelete" :disabled="form.processing"
                            class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-white hover:bg-red-700 focus:outline-none disabled:opacity-50">
                            Delete
                        </button>
                        <button type="button" @click="closeModal" :disabled="form.processing"
                            class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <div v-else class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Delete Live Stream</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button @click="deleteStream" :disabled="deleteForm.processing"
                        class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-white hover:bg-red-700 focus:outline-none disabled:opacity-50">
                        {{ deleteForm.processing ? 'Deleting...' : 'Delete' }}
                    </button>
                    <button @click="showDeleteConfirm = false" :disabled="deleteForm.processing"
                        class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
