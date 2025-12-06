<script setup>
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DialogModal from './DialogModal.vue';
import InputLabel from './InputLabel.vue';
import TextInput from './TextInput.vue';
import InputError from './InputError.vue';

const props = defineProps({
    show: Boolean,
    editingItem: Object,
});

const emit = defineEmits(['close']);

const showDeleteConfirm = ref(false);
const imagePreview = ref(null);

const form = useForm({
    type: 'photo',
    title: '',
    description: '',
    credit: '',
    image: null,
    video_url: '',
    order: 0,
    is_published: true,
});

watch(() => props.show, (newValue) => {
    if (newValue) {
        if (props.editingItem) {
            // Edit mode
            form.type = props.editingItem.type;
            form.title = props.editingItem.title;
            form.description = props.editingItem.description || '';
            form.credit = props.editingItem.credit || '';
            form.video_url = props.editingItem.video_url || '';
            form.order = props.editingItem.order || 0;
            form.is_published = props.editingItem.is_published;
            form.image = null;
            imagePreview.value = props.editingItem.image_path;
        } else {
            // Add mode
            form.reset();
            form.type = 'photo';
            form.is_published = true;
            form.order = 0;
            imagePreview.value = null;
        }
        showDeleteConfirm.value = false;
    }
});

const isEditMode = computed(() => !!props.editingItem);

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    form.image = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(route('gallery.update', props.editingItem.id), {
            onSuccess: () => {
                emit('close');
                form.reset();
                imagePreview.value = null;
            },
        });
    } else {
        form.post(route('gallery.store'), {
            onSuccess: () => {
                emit('close');
                form.reset();
                imagePreview.value = null;
            },
        });
    }
};

const confirmDelete = () => {
    showDeleteConfirm.value = true;
};

const deleteItem = () => {
    form.delete(route('gallery.destroy', props.editingItem.id), {
        onSuccess: () => {
            emit('close');
            showDeleteConfirm.value = false;
        },
    });
};

const closeModal = () => {
    if (!showDeleteConfirm.value) {
        emit('close');
        form.reset();
        imagePreview.value = null;
    }
};
</script>

<template>
    <DialogModal :show="show" @close="closeModal" max-width="2xl">
        <template #title>
            {{ isEditMode ? 'Edit Gallery Item' : 'Add New Gallery Item' }}
        </template>

        <template #content>
            <form @submit.prevent="submitForm" class="space-y-4">
                <!-- Type Selection (only for add mode) -->
                <div v-if="!isEditMode">
                    <InputLabel for="type" value="Type" />
                    <select
                        v-model="form.type"
                        id="type"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    >
                        <option value="photo">Photo</option>
                        <option value="video">Video (YouTube)</option>
                    </select>
                    <InputError :message="form.errors.type" class="mt-2" />
                </div>

                <!-- Title -->
                <div>
                    <InputLabel for="title" value="Title *" />
                    <TextInput
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <!-- Description -->
                <div>
                    <InputLabel for="description" value="Description (optional)" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    ></textarea>
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <!-- Credit -->
                <div>
                    <InputLabel for="credit" value="Credit (optional)" />
                    <TextInput
                        id="credit"
                        v-model="form.credit"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Photographer/Videographer name"
                    />
                    <InputError :message="form.errors.credit" class="mt-2" />
                </div>

                <!-- Photo Upload (for photo type) -->
                <div v-if="form.type === 'photo'">
                    <InputLabel for="image" :value="isEditMode ? 'Change Image (optional)' : 'Image *'" />
                    <input
                        id="image"
                        type="file"
                        accept="image/*"
                        @change="handleImageUpload"
                        class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100
                            dark:file:bg-gray-700 dark:file:text-gray-300"
                    />
                    <InputError :message="form.errors.image" class="mt-2" />

                    <!-- Image Preview -->
                    <div v-if="imagePreview" class="mt-4">
                        <img :src="imagePreview" alt="Preview" class="max-h-48 rounded-lg shadow-md" />
                    </div>
                </div>

                <!-- Video URL (for video type) -->
                <div v-if="form.type === 'video'">
                    <InputLabel for="video_url" value="YouTube Video URL *" />
                    <TextInput
                        id="video_url"
                        v-model="form.video_url"
                        type="url"
                        class="mt-1 block w-full"
                        placeholder="https://www.youtube.com/watch?v=..."
                        required
                    />
                    <InputError :message="form.errors.video_url" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Paste the full YouTube video URL
                    </p>
                </div>

                <!-- Order -->
                <div>
                    <InputLabel for="order" value="Order" />
                    <TextInput
                        id="order"
                        v-model.number="form.order"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.order" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Lower numbers appear first
                    </p>
                </div>

                <!-- Published Status -->
                <div>
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="form.is_published"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Published (visible to public)</span>
                    </label>
                </div>
            </form>

            <!-- Delete Confirmation (nested modal) -->
            <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Confirm Delete</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Are you sure you want to delete this gallery item? This action cannot be undone.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="showDeleteConfirm = false"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button
                            @click="deleteItem"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex items-center justify-between w-full">
                <button
                    v-if="isEditMode"
                    @click="confirmDelete"
                    type="button"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                    Delete
                </button>
                <div v-else></div>

                <div class="flex gap-3">
                    <button
                        @click="closeModal"
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button
                        @click="submitForm"
                        type="button"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50">
                        {{ isEditMode ? 'Update' : 'Create' }}
                    </button>
                </div>
            </div>
        </template>
    </DialogModal>
</template>
