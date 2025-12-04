<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DialogModal from './DialogModal.vue';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    editingShow: Object,
    nextShowId: Number,
});

const emit = defineEmits(['close']);

const form = useForm({
    show_id: '',
    show_date: '',
    setlist: '',
    unit_song: '',
    is_global_center: false,
    is_us_center: false,
    is_the_show_has_event: '',
    additional_information: '',
});

watch(() => props.editingShow, (newVal) => {
    if (newVal) {
        form.show_id = newVal.show_id;
        form.show_date = newVal.show_date;
        form.setlist = newVal.setlist;
        form.unit_song = newVal.unit_song;
        form.is_global_center = !!newVal.is_global_center;
        form.is_us_center = !!newVal.is_us_center;
        form.is_the_show_has_event = newVal.is_the_show_has_event || '';
        form.additional_information = newVal.additional_information || '';
    } else {
        form.reset();
        form.show_id = props.nextShowId || 1;
    }
}, { immediate: true });

watch(() => props.nextShowId, (newVal) => {
    if (!props.editingShow && newVal) {
        form.show_id = newVal;
    }
});

const submit = () => {
    if (props.editingShow) {
        form.put(route('show-teater.update', props.editingShow.show_id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
                form.reset();
            },
        });
    } else {
        form.post(route('show-teater.store'), {
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
            {{ editingShow ? 'Edit Show Teater' : 'Tambah Show Teater' }}
        </template>

        <template #content>
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="show_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Show
                                ID</label>
                            <input id="show_id" v-model="form.show_id" type="number" disabled
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white bg-gray-100 dark:bg-gray-600 cursor-not-allowed" />
                            <div v-if="form.errors.show_id" class="text-sm text-red-600 mt-1">{{ form.errors.show_id }}
                            </div>
                        </div>

                        <div>
                            <label for="show_date"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Show</label>
                            <input id="show_date" v-model="form.show_date" type="date" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            <div v-if="form.errors.show_date" class="text-sm text-red-600 mt-1">{{ form.errors.show_date
                                }}</div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <label for="setlist"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mr-3">Setlist</label>
                            <input id="is_global_center" v-model="form.is_global_center" type="checkbox"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600" />
                            <label for="is_global_center"
                                class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Global
                                Center</label>
                        </div>
                        <input id="setlist" v-model="form.setlist" type="text" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        <div v-if="form.errors.setlist" class="text-sm text-red-600 mt-1">{{ form.errors.setlist }}
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <label for="unit_song"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mr-3">Unit
                                Song</label>
                            <input id="is_us_center" v-model="form.is_us_center" type="checkbox"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600" />
                            <label for="is_us_center" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">US
                                Center</label>
                        </div>
                        <input id="unit_song" v-model="form.unit_song" type="text" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        <div v-if="form.errors.unit_song" class="text-sm text-red-600 mt-1">{{ form.errors.unit_song }}
                        </div>
                    </div>



                    <div>
                        <label for="is_the_show_has_event"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event (opsional)</label>
                        <input id="is_the_show_has_event" v-model="form.is_the_show_has_event" type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        <div v-if="form.errors.is_the_show_has_event" class="text-sm text-red-600 mt-1">{{
                            form.errors.is_the_show_has_event }}</div>
                    </div>

                    <div>
                        <label for="additional_information"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Info Tambahan
                            (opsional)</label>
                        <input id="additional_information" v-model="form.additional_information" type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        <div v-if="form.errors.additional_information" class="text-sm text-red-600 mt-1">{{
                            form.errors.additional_information }}</div>
                    </div>
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton @click="close">
                Batal
            </SecondaryButton>

            <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                @click="submit">
                {{ editingShow ? 'Update' : 'Simpan' }}
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
