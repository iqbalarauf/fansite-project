<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DialogModal from './DialogModal.vue';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    editingEvent: Object,
});

const emit = defineEmits(['close']);

const form = useForm({
    event_name: '',
    event_date: '',
    location: '',
    status: 'off-air',
    purchase_link: '',
});

watch(() => props.editingEvent, (newVal) => {
    if (newVal) {
        form.event_name = newVal.event_name || '';
        form.event_date = newVal.event_date || '';
        form.location = newVal.location || '';
        form.status = newVal.status || 'off-air';
        form.purchase_link = newVal.purchase_link || '';
    } else {
        form.reset();
    }
}, { immediate: true });

const submit = () => {
    if (props.editingEvent) {
        form.put(route('concert-events.update', props.editingEvent.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
                form.reset();
            },
        });
    } else {
        form.post(route('concert-events.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
                form.reset();
            },
        });
    }
};

const close = () => {
    emit('close');
    form.reset();
    form.clearErrors();
};
</script>

<template>
    <DialogModal :show="show" @close="close" max-width="2xl">
        <template #title>
            {{ editingEvent ? 'Edit Concert Event' : 'Add Concert Event' }}
        </template>

        <template #content>
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <!-- Event Name -->
                    <div>
                        <label for="event_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Event Name
                        </label>
                        <input id="event_name" v-model="form.event_name" type="text" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        <div v-if="form.errors.event_name" class="text-sm text-red-600 mt-1">
                            {{ form.errors.event_name }}
                        </div>
                    </div>

                    <!-- Event Date -->
                    <div>
                        <label for="event_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Event Date
                        </label>
                        <input id="event_date" v-model="form.event_date" type="date" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        <div v-if="form.errors.event_date" class="text-sm text-red-600 mt-1">
                            {{ form.errors.event_date }}
                        </div>
                    </div>

                    <!-- Location -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Location
                        </label>
                        <input id="location" v-model="form.location" type="text" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        <div v-if="form.errors.location" class="text-sm text-red-600 mt-1">
                            {{ form.errors.location }}
                        </div>
                    </div>

                    <!-- Status (Radio Buttons) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <div class="flex gap-6">
                            <label class="inline-flex items-center">
                                <input v-model="form.status" type="radio" value="off-air"
                                    class="rounded-full border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Off-Air</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input v-model="form.status" type="radio" value="on-air"
                                    class="rounded-full border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">On-Air</span>
                            </label>
                        </div>
                        <div v-if="form.errors.status" class="text-sm text-red-600 mt-1">
                            {{ form.errors.status }}
                        </div>
                    </div>

                    <!-- Purchase Link -->
                    <div>
                        <label for="purchase_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Purchase Link (Optional)
                        </label>
                        <input id="purchase_link" v-model="form.purchase_link" type="url"
                            placeholder="https://example.com/tickets"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        <div v-if="form.errors.purchase_link" class="text-sm text-red-600 mt-1">
                            {{ form.errors.purchase_link }}
                        </div>
                    </div>
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton @click="close">
                Cancel
            </SecondaryButton>

            <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                @click="submit">
                {{ editingEvent ? 'Update' : 'Save' }}
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
