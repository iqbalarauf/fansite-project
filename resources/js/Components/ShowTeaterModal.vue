<script setup>
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DialogModal from './DialogModal.vue';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    editingShow: Object,
    nextShowId: Number,
    setlistsWithUnitSongs: Array,
});

const emit = defineEmits(['close']);

const isDoubleUs = ref(false);
const selectedSetlistId = ref('');

const form = useForm({
    show_id: '',
    show_date: '',
    setlist: '',
    unit_song: '',
    unit_song_2: '',
    is_global_center: false,
    is_us_center: false,
    is_the_show_has_event: '',
    additional_information: '',
});

// Get available unit songs based on selected setlist
const availableUnitSongs = computed(() => {
    if (!selectedSetlistId.value || !props.setlistsWithUnitSongs) return [];
    const setlist = props.setlistsWithUnitSongs.find(s => s.id === selectedSetlistId.value);
    return setlist ? setlist.unit_songs : [];
});

watch(() => props.editingShow, (newVal) => {
    if (newVal) {
        form.show_id = newVal.show_id;
        form.show_date = newVal.show_date;
        form.setlist = newVal.setlist;
        form.is_global_center = !!newVal.is_global_center;
        form.is_us_center = !!newVal.is_us_center;
        form.is_the_show_has_event = newVal.is_the_show_has_event || '';
        form.additional_information = newVal.additional_information || '';

        // Find setlist ID
        const setlist = props.setlistsWithUnitSongs?.find(s => s.name === newVal.setlist);
        selectedSetlistId.value = setlist ? setlist.id : '';

        // Handle double US format
        if (newVal.unit_song && newVal.unit_song.includes(', ')) {
            const songs = newVal.unit_song.split(', ');
            isDoubleUs.value = true;
            form.unit_song = songs[0] || '';
            form.unit_song_2 = songs[1] || '';
        } else {
            isDoubleUs.value = false;
            form.unit_song = newVal.unit_song;
            form.unit_song_2 = '';
        }
    } else {
        form.reset();
        form.show_id = props.nextShowId || 1;
        isDoubleUs.value = false;
        selectedSetlistId.value = '';
    }
}, { immediate: true });

watch(() => props.nextShowId, (newVal) => {
    if (!props.editingShow && newVal) {
        form.show_id = newVal;
    }
});

// Update setlist name when selection changes
watch(selectedSetlistId, (newVal) => {
    if (newVal) {
        const setlist = props.setlistsWithUnitSongs?.find(s => s.id === newVal);
        form.setlist = setlist ? setlist.name : '';
        // Reset unit songs when setlist changes
        if (!props.editingShow) {
            form.unit_song = '';
            form.unit_song_2 = '';
        }
    }
});

// Reset second unit song when Double US is unchecked
watch(isDoubleUs, (newVal) => {
    if (!newVal) {
        form.unit_song_2 = '';
    }
});

const submit = () => {
    // Combine unit songs if double US is checked
    const submitData = { ...form.data() };
    if (isDoubleUs.value && form.unit_song_2) {
        submitData.unit_song = `${form.unit_song}, ${form.unit_song_2}`;
    }

    if (props.editingShow) {
        form.transform(() => submitData).put(route('show-teater.update', props.editingShow.show_id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
                form.reset();
                isDoubleUs.value = false;
                selectedSetlistId.value = '';
            },
        });
    } else {
        form.transform(() => submitData).post(route('show-teater.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
                form.reset();
                isDoubleUs.value = false;
                selectedSetlistId.value = '';
            },
        });
    }
};

const close = () => {
    emit('close');
    form.reset();
    form.clearErrors();
    isDoubleUs.value = false;
    selectedSetlistId.value = '';
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
                        <select id="setlist" v-model="selectedSetlistId" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Setlist</option>
                            <option v-for="setlist in setlistsWithUnitSongs" :key="setlist.id" :value="setlist.id">
                                {{ setlist.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.setlist" class="text-sm text-red-600 mt-1">{{ form.errors.setlist }}
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <label for="unit_song"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mr-3">Unit
                                Song</label>
                            <input id="is_double_us" v-model="isDoubleUs" type="checkbox"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600" />
                            <label for="is_double_us" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Double
                                US</label>
                            <input v-if="!isDoubleUs" id="is_us_center" v-model="form.is_us_center" type="checkbox"
                                class="ml-3 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600" />
                            <label v-if="!isDoubleUs" for="is_us_center"
                                class="ml-2 block text-sm text-gray-700 dark:text-gray-300">US
                                Center</label>
                        </div>

                        <div v-if="!isDoubleUs">
                            <select id="unit_song" v-model="form.unit_song" required :disabled="!selectedSetlistId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed">
                                <option value="">Pilih Unit Song</option>
                                <option v-for="unitSong in availableUnitSongs" :key="unitSong.id"
                                    :value="unitSong.name">
                                    {{ unitSong.name }}
                                </option>
                            </select>
                        </div>

                        <div v-else class="space-y-2">
                            <select id="unit_song" v-model="form.unit_song" required :disabled="!selectedSetlistId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed">
                                <option value="">Pilih Unit Song 1</option>
                                <option v-for="unitSong in availableUnitSongs" :key="unitSong.id"
                                    :value="unitSong.name">
                                    {{ unitSong.name }}
                                </option>
                            </select>
                            <select id="unit_song_2" v-model="form.unit_song_2" required :disabled="!selectedSetlistId"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed">
                                <option value="">Pilih Unit Song 2</option>
                                <option v-for="unitSong in availableUnitSongs" :key="unitSong.id + '_2'"
                                    :value="unitSong.name">
                                    {{ unitSong.name }}
                                </option>
                            </select>
                        </div>

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
