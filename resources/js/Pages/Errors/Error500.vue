<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    message: String,
});
</script>

<template>
    <Head title="Server Error" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 text-center">
            <div class="mb-6">
                <div class="text-6xl font-bold text-purple-500 mb-4">500</div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Server Error</h1>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    {{ message || 'Something went wrong on our end. Please try again later.' }}
                </p>
            </div>

            <div class="space-y-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    An unexpected error occurred while processing your request.
                </p>
                <div class="flex gap-3 justify-center">
                    <Link href="/" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                        Back to Home
                    </Link>
                    <button @click="$router.back()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
