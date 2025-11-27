<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Sidebar from '@/Components/Sidebar.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

// Dark mode state (persisted to localStorage)
const isDark = ref(false);

const applyDark = (value) => {
    try {
        const html = document.documentElement;
        if (value) html.classList.add('dark'); else html.classList.remove('dark');
        localStorage.setItem('dark-mode', value ? '1' : '0');
        isDark.value = !!value;
    } catch (e) {
        // ignore server-side or non-browser environments
    }
};

const toggleDark = () => applyDark(!isDark.value);

onMounted(() => {
    try {
        const stored = localStorage.getItem('dark-mode');
        if (stored !== null) {
            applyDark(stored === '1');
        } else {
            // default based on current html class
            isDark.value = document.documentElement.classList.contains('dark');
        }
    } catch (e) {
        // ignore
    }
});
</script>

<template>
    <div>

        <Head :title="title" />

        <Banner />

        <Sidebar />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-500 lg:pl-64">
            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
