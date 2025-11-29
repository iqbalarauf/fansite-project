<template>
    <footer class="py-4">
        <p class="text-sm text-center text-gray-600 dark:text-gray-400">
            {{ copyrightText }}
        </p>
    </footer>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    type: {
        type: String,
        default: 'public', // 'public' or 'auth'
    }
});

const page = usePage();
const currentYear = new Date().getFullYear();
const appName = computed(() => page.props.appSettings?.app_name || 'FanSight');

const copyrightText = computed(() => {
    if (props.type === 'auth') {
        return `© ${currentYear} ${appName.value}. All rights reserved.`;
    }
    return `© ${currentYear} ${appName.value} - Dikembangkan oleh LabqiTech. All rights reserved.`;
});
</script>
