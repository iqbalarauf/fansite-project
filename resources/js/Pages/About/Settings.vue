<template>
    <AppLayout title="About Settings">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                About Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex">
                            <button
                                @click="activeTab = 'idol'"
                                :class="[
                                    'py-4 px-6 text-center border-b-2 font-medium text-sm',
                                    activeTab === 'idol'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                About Idol
                            </button>
                            <button
                                @click="activeTab = 'fanbase'"
                                :class="[
                                    'py-4 px-6 text-center border-b-2 font-medium text-sm',
                                    activeTab === 'fanbase'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                About Fanbase
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <form @submit.prevent="submitForm" class="p-6">
                        <!-- Idol Tab -->
                        <div v-show="activeTab === 'idol'" class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">About Idol Settings</h3>

                            <!-- Section 1: Basic Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold mb-4">Section 1: Basic Information</h4>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Idol Name</label>
                                    <input
                                        v-model="form.idol_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Photo</label>
                                    <input
                                        @change="handleIdolPhoto"
                                        type="file"
                                        accept="image/*"
                                        class="mt-1 block w-full"
                                    />
                                    <img v-if="idolPhotoPreview || settings.idol_photo" :src="idolPhotoPreview || settings.idol_photo" class="mt-2 h-32 object-cover rounded" />
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea
                                        v-model="form.idol_description"
                                        rows="4"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Section 2: Achievements & Discography -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold mb-4">Section 2: Achievements & Discography</h4>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Achievements</label>
                                    <textarea
                                        v-model="form.idol_achievements"
                                        rows="4"
                                        placeholder="List achievements, one per line or use markdown"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Discography</label>
                                    <textarea
                                        v-model="form.idol_discography"
                                        rows="4"
                                        placeholder="List albums, singles, etc."
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Section 3: Social Media -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold mb-4">Section 3: Social Media Embeds</h4>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Social Media Embed Codes</label>
                                    <textarea
                                        v-model="form.idol_social_media"
                                        rows="6"
                                        placeholder="Paste embed codes (Instagram, Twitter, etc.)"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Paste embed codes from social media platforms</p>
                                </div>
                            </div>

                            <!-- Display Options -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold mb-4">Display Options</h4>

                                <div class="flex items-center">
                                    <input
                                        v-model="form.idol_show_on_welcome"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <label class="ml-2 text-sm text-gray-700">Show idol section on Welcome page</label>
                                </div>
                            </div>
                        </div>

                        <!-- Fanbase Tab -->
                        <div v-show="activeTab === 'fanbase'" class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">About Fanbase Settings</h3>

                            <!-- Section 1: Basic Info -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold mb-4">Section 1: Basic Information</h4>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Fanbase Name</label>
                                    <input
                                        v-model="form.fanbase_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Logo</label>
                                    <input
                                        @change="handleFanbaseLogo"
                                        type="file"
                                        accept="image/*"
                                        class="mt-1 block w-full"
                                    />
                                    <img v-if="fanbaseLogoPreview || settings.fanbase_logo" :src="fanbaseLogoPreview || settings.fanbase_logo" class="mt-2 h-32 object-cover rounded" />
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea
                                        v-model="form.fanbase_description"
                                        rows="4"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Section 2: Activities & Gallery -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold mb-4">Section 2: Activities & Gallery</h4>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Activities</label>
                                    <textarea
                                        v-model="form.fanbase_activities"
                                        rows="4"
                                        placeholder="Describe fanbase activities and events"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Gallery (Max 5 images)</label>
                                    <input
                                        @change="handleGalleryImages"
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        :disabled="currentGalleryCount >= 5"
                                        class="mt-1 block w-full"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">Current images: {{ currentGalleryCount }}/5</p>

                                    <!-- Gallery Preview -->
                                    <div v-if="settings.fanbase_gallery && settings.fanbase_gallery.length > 0" class="mt-4 grid grid-cols-3 gap-4">
                                        <div v-for="(image, index) in settings.fanbase_gallery" :key="index" class="relative">
                                            <img :src="image" class="h-24 w-full object-cover rounded" />
                                            <button
                                                @click.prevent="deleteGalleryImage(index)"
                                                type="button"
                                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: CTA Section (Optional) -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold mb-4">Section 3: Call-to-Action (Optional)</h4>

                                <div class="mb-4 flex items-center">
                                    <input
                                        v-model="form.fanbase_cta_enabled"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <label class="ml-2 text-sm text-gray-700">Enable CTA Section</label>
                                </div>

                                <div v-if="form.fanbase_cta_enabled" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Background Image</label>
                                        <input
                                            @change="handleCtaBackground"
                                            type="file"
                                            accept="image/*"
                                            class="mt-1 block w-full"
                                        />
                                        <img v-if="ctaBackgroundPreview || settings.fanbase_cta_background" :src="ctaBackgroundPreview || settings.fanbase_cta_background" class="mt-2 h-32 object-cover rounded" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">CTA Title</label>
                                        <input
                                            v-model="form.fanbase_cta_title"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">CTA Description</label>
                                        <textarea
                                            v-model="form.fanbase_cta_description"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        ></textarea>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Button 1 Text</label>
                                            <input
                                                v-model="form.fanbase_cta_button1_text"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Button 1 Link</label>
                                            <input
                                                v-model="form.fanbase_cta_button1_link"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Button 2 Text</label>
                                            <input
                                                v-model="form.fanbase_cta_button2_text"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Button 2 Link</label>
                                            <input
                                                v-model="form.fanbase_cta_button2_link"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 flex items-center justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Settings' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    settings: Object
});

const activeTab = ref('idol');

const form = useForm({
    // Idol fields
    idol_name: props.settings.idol_name || '',
    idol_photo: null,
    idol_description: props.settings.idol_description || '',
    idol_achievements: props.settings.idol_achievements || '',
    idol_discography: props.settings.idol_discography || '',
    idol_social_media: props.settings.idol_social_media || '',
    idol_show_on_welcome: props.settings.idol_show_on_welcome === 'true' || props.settings.idol_show_on_welcome === true,

    // Fanbase fields
    fanbase_name: props.settings.fanbase_name || '',
    fanbase_logo: null,
    fanbase_description: props.settings.fanbase_description || '',
    fanbase_activities: props.settings.fanbase_activities || '',
    fanbase_gallery: [],
    fanbase_cta_enabled: props.settings.fanbase_cta_enabled === 'true' || props.settings.fanbase_cta_enabled === true,
    fanbase_cta_background: null,
    fanbase_cta_title: props.settings.fanbase_cta_title || '',
    fanbase_cta_description: props.settings.fanbase_cta_description || '',
    fanbase_cta_button1_text: props.settings.fanbase_cta_button1_text || '',
    fanbase_cta_button1_link: props.settings.fanbase_cta_button1_link || '',
    fanbase_cta_button2_text: props.settings.fanbase_cta_button2_text || '',
    fanbase_cta_button2_link: props.settings.fanbase_cta_button2_link || '',
});

const idolPhotoPreview = ref(null);
const fanbaseLogoPreview = ref(null);
const ctaBackgroundPreview = ref(null);

const currentGalleryCount = computed(() => {
    return props.settings.fanbase_gallery ? props.settings.fanbase_gallery.length : 0;
});

const handleIdolPhoto = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.idol_photo = file;
        idolPhotoPreview.value = URL.createObjectURL(file);
    }
};

const handleFanbaseLogo = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.fanbase_logo = file;
        fanbaseLogoPreview.value = URL.createObjectURL(file);
    }
};

const handleCtaBackground = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.fanbase_cta_background = file;
        ctaBackgroundPreview.value = URL.createObjectURL(file);
    }
};

const handleGalleryImages = (e) => {
    const files = Array.from(e.target.files);
    const remainingSlots = 5 - currentGalleryCount.value;
    const filesToAdd = files.slice(0, remainingSlots);

    form.fanbase_gallery = filesToAdd;
};

const deleteGalleryImage = (index) => {
    if (confirm('Are you sure you want to delete this image?')) {
        form.delete(route('about.settings.gallery.delete', index), {
            preserveScroll: true,
            onSuccess: () => {
                // Reload page to refresh gallery
            }
        });
    }
};

const submitForm = () => {
    form.post(route('about.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset file inputs
            idolPhotoPreview.value = null;
            fanbaseLogoPreview.value = null;
            ctaBackgroundPreview.value = null;
            form.idol_photo = null;
            form.fanbase_logo = null;
            form.fanbase_cta_background = null;
            form.fanbase_gallery = [];
        }
    });
};
</script>
