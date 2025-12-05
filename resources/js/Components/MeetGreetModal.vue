<script setup>
import { ref, watch, computed } from 'vue';
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
    event_type: 'meet-greet',
    event_date: '',
    event_date_2: '',
    ticket_sale_datetime: '',
    purchase_link: '',
});

// Computed property to check if video call is selected
const isVideoCall = computed(() => form.event_type === 'video-call');

watch(() => props.editingEvent, (newVal) => {
    if (newVal) {
        form.event_name = newVal.event_name || '';
        form.event_type = newVal.event_type || 'meet-greet';
        form.event_date = newVal.event_date || '';
        form.event_date_2 = newVal.event_date_2 || '';
        form.ticket_sale_datetime = newVal.ticket_sale_datetime || '';
        form.purchase_link = newVal.purchase_link || '';
    } else {
        form.reset();
    }
}, { immediate: true });

// Watch event_type and clear second date if switching to meet-greet
watch(() => form.event_type, (newType) => {
    if (newType === 'meet-greet') {
        form.event_date_2 = '';
    }
});

const submit = () => {
    // Clear event_date_2 if not video call
    const submitData = { ...form.data() };
    if (form.event_type !== 'video-call') {
        submitData.event_date_2 = null;
    }

    if (props.editingEvent) {
        form.transform(() => submitData).put(route('meet-greet.update', props.editingEvent.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
                form.reset();
            },
        });
    } else {
        form.transform(() => submitData).post(route('meet-greet.store'), {
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
            {{ editingEvent ? 'Edit Meet & Greet Event' : 'Add Meet & Greet Event' }}
        </template>

        <template #content>
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <!-- Event Type (Radio Buttons) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Event Type
                        </label>
                        <div class="flex gap-6">
                            <label class="inline-flex items-center">
                                <input v-model="form.event_type" type="radio" value="meet-greet"
                                    class="rounded-full border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Meet & Greet Festival</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input v-model="form.event_type" type="radio" value="video-call"
                                    class="rounded-full border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600" />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Video Call</span>
                            </label>
                        </div>
                        <div v-if="form.errors.event_type" class="text-sm text-red-600 mt-1">
                            {{ form.errors.event_type }}
                        </div>
                    </div>

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

                    <!-- Event Date(s) - Conditional based on type -->
                    <div v-if="!isVideoCall">
                        <!-- Single Date for Meet & Greet -->
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
                    </div>

                    <div v-else>
                        <!-- Two Dates for Video Call -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="event_date_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Event Date 1
                                </label>
                                <input id="event_date_1" v-model="form.event_date" type="date" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                                <div v-if="form.errors.event_date" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.event_date }}
                                </div>
                            </div>
                            <div>
                                <label for="event_date_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Event Date 2
                                </label>
                                <input id="event_date_2" v-model="form.event_date_2" type="date" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                                <div v-if="form.errors.event_date_2" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.event_date_2 }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Sale Datetime -->
                    <div>
                        <label for="ticket_sale_datetime" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Jadwal Pembelian Tiket (Optional)
                        </label>
                        <input id="ticket_sale_datetime" v-model="form.ticket_sale_datetime" type="datetime-local"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        <div v-if="form.errors.ticket_sale_datetime" class="text-sm text-red-600 mt-1">
                            {{ form.errors.ticket_sale_datetime }}
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
