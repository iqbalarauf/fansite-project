<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';


const page = usePage();
const isOpen = ref(false);

const toggleSidebar = () => {
    isOpen.value = !isOpen.value;
};

const closeSidebar = () => {
    isOpen.value = false;
};

const logout = () => {
    router.post(route('logout'));
};

const menuItems = computed(() => {
    const items = [
        {
            name: 'Dashboard',
            href: route('dashboard'),
            icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
            active: route().current('dashboard'),
        },
        {
            name: 'My Posts',
            href: route('posts.manage'),
            icon: 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10',
            active: route().current('posts.manage') || route().current('posts.create') || route().current('posts.edit'),
        },
    ];

    return items;
});
</script>

<template>
    <!-- Sidebar -->
    <aside :class="[
        'fixed left-0 z-30 min-h-screen w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
        <div class="flex flex-col h-full">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-20 px-4">
                <Link :href="route('dashboard')" class="flex items-center" @click="closeSidebar">
                <img v-if="$page.props.appSettings?.logo" :src="$page.props.appSettings.logo"
                    class="h-12 w-auto lg:h-16 object-contain" alt="logo" />
                <img v-else src="/storage/logo.svg" class="h-12 w-auto lg:h-16 object-contain" alt="default logo" />
                <span class="text-xl font-semibold text-gray-900 dark:text-white ms-3">
                    {{ $page.props.appSettings?.sidebar_name || $page.props.appSettings?.app_name || 'Dashboard' }}
                </span>
                </Link>

                <ThemeToggle variant="mobile" v-if="$page.props.auth.user" />

                <button @click="closeSidebar"
                    class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
                <Link v-for="item in menuItems" :key="item.name" :href="item.href" @click="closeSidebar" :class="[
                    'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                    item.active
                        ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
                ]">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                </svg>
                {{ item.name }}
                </Link>
            </nav>

            <!-- Sidebar Footer -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center px-4 py-2">
                    <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                        <img class="w-10 h-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                            :alt="$page.props.auth.user.name" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{
                            $page.props.auth.user.name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $page.props.auth.user.email }}
                        </div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <Link :href="route('settings.index')" @click="closeSidebar"
                        class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M 22.205078 2 A 1.0001 1.0001 0 0 0 21.21875 2.8378906 L 20.246094 8.7929688 C 19.076509 9.1331971 17.961243 9.5922728 16.910156 10.164062 L 11.996094 6.6542969 A 1.0001 1.0001 0 0 0 10.708984 6.7597656 L 6.8183594 10.646484 A 1.0001 1.0001 0 0 0 6.7070312 11.927734 L 10.164062 16.873047 C 9.583454 17.930271 9.1142098 19.051824 8.765625 20.232422 L 2.8359375 21.21875 A 1.0001 1.0001 0 0 0 2.0019531 22.205078 L 2.0019531 27.705078 A 1.0001 1.0001 0 0 0 2.8261719 28.691406 L 8.7597656 29.742188 C 9.1064607 30.920739 9.5727226 32.043065 10.154297 33.101562 L 6.6542969 37.998047 A 1.0001 1.0001 0 0 0 6.7597656 39.285156 L 10.648438 43.175781 A 1.0001 1.0001 0 0 0 11.927734 43.289062 L 16.882812 39.820312 C 17.936999 40.39548 19.054994 40.857928 20.228516 41.201172 L 21.21875 47.164062 A 1.0001 1.0001 0 0 0 22.205078 48 L 27.705078 48 A 1.0001 1.0001 0 0 0 28.691406 47.173828 L 29.751953 41.1875 C 30.920633 40.838997 32.033372 40.369697 33.082031 39.791016 L 38.070312 43.291016 A 1.0001 1.0001 0 0 0 39.351562 43.179688 L 43.240234 39.287109 A 1.0001 1.0001 0 0 0 43.34375 37.996094 L 39.787109 33.058594 C 40.355783 32.014958 40.813915 30.908875 41.154297 29.748047 L 47.171875 28.693359 A 1.0001 1.0001 0 0 0 47.998047 27.707031 L 47.998047 22.207031 A 1.0001 1.0001 0 0 0 47.160156 21.220703 L 41.152344 20.238281 C 40.80968 19.078827 40.350281 17.974723 39.78125 16.931641 L 43.289062 11.933594 A 1.0001 1.0001 0 0 0 43.177734 10.652344 L 39.287109 6.7636719 A 1.0001 1.0001 0 0 0 37.996094 6.6601562 L 33.072266 10.201172 C 32.023186 9.6248101 30.909713 9.1579916 29.738281 8.8125 L 28.691406 2.828125 A 1.0001 1.0001 0 0 0 27.705078 2 L 22.205078 2 z M 23.056641 4 L 26.865234 4 L 27.861328 9.6855469 A 1.0001 1.0001 0 0 0 28.603516 10.484375 C 30.066026 10.848832 31.439607 11.426549 32.693359 12.185547 A 1.0001 1.0001 0 0 0 33.794922 12.142578 L 38.474609 8.7792969 L 41.167969 11.472656 L 37.835938 16.220703 A 1.0001 1.0001 0 0 0 37.796875 17.310547 C 38.548366 18.561471 39.118333 19.926379 39.482422 21.380859 A 1.0001 1.0001 0 0 0 40.291016 22.125 L 45.998047 23.058594 L 45.998047 26.867188 L 40.279297 27.871094 A 1.0001 1.0001 0 0 0 39.482422 28.617188 C 39.122545 30.069817 38.552234 31.434687 37.800781 32.685547 A 1.0001 1.0001 0 0 0 37.845703 33.785156 L 41.224609 38.474609 L 38.53125 41.169922 L 33.791016 37.84375 A 1.0001 1.0001 0 0 0 32.697266 37.808594 C 31.44975 38.567585 30.074755 39.148028 28.617188 39.517578 A 1.0001 1.0001 0 0 0 27.876953 40.3125 L 26.867188 46 L 23.052734 46 L 22.111328 40.337891 A 1.0001 1.0001 0 0 0 21.365234 39.53125 C 19.90185 39.170557 18.522094 38.59371 17.259766 37.835938 A 1.0001 1.0001 0 0 0 16.171875 37.875 L 11.46875 41.169922 L 8.7734375 38.470703 L 12.097656 33.824219 A 1.0001 1.0001 0 0 0 12.138672 32.724609 C 11.372652 31.458855 10.793319 30.079213 10.427734 28.609375 A 1.0001 1.0001 0 0 0 9.6328125 27.867188 L 4.0019531 26.867188 L 4.0019531 23.052734 L 9.6289062 22.117188 A 1.0001 1.0001 0 0 0 10.435547 21.373047 C 10.804273 19.898143 11.383325 18.518729 12.146484 17.255859 A 1.0001 1.0001 0 0 0 12.111328 16.164062 L 8.8261719 11.46875 L 11.523438 8.7734375 L 16.185547 12.105469 A 1.0001 1.0001 0 0 0 17.28125 12.148438 C 18.536908 11.394293 19.919867 10.822081 21.384766 10.462891 L 23.056641 4 z" />
                    </svg>
                    Settings
                    </Link>

                    <Link v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')"
                        @click="closeSidebar" class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50
                    dark:hover:bg-gray-700 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="currentColor"
                        viewBox="0 0 48 48">
                        <path
                            d="M 41 2 C 37.145851 2 34 5.1458514 34 9 C 34 10.842988 34.724355 12.518937 35.896484 13.771484 L 27.525391 24 L 15.919922 24 C 15.430748 20.617539 12.513828 18 9 18 C 5.1458514 18 2 21.145851 2 25 C 2 28.854149 5.1458514 32 9 32 C 12.513828 32 15.430748 29.382461 15.919922 26 L 27.501953 26 L 35.535156 36.640625 C 34.5767 37.838633 34 39.353257 34 41 C 34 44.854149 37.145851 48 41 48 C 44.854149 48 48 44.854149 48 41 C 48 37.145851 44.854149 34 41 34 C 39.515247 34 38.13907 34.471256 37.003906 35.265625 L 29.271484 25.025391 L 37.455078 15.021484 C 38.496931 15.638103 39.706395 16 41 16 C 44.854149 16 48 12.854149 48 9 C 48 5.1458514 44.854149 2 41 2 z M 41 4 C 43.773268 4 46 6.2267316 46 9 C 46 11.773268 43.773268 14 41 14 C 38.226732 14 36 11.773268 36 9 C 36 6.2267316 38.226732 4 41 4 z M 9 20 C 11.773268 20 14 22.226732 14 25 C 14 27.773268 11.773268 30 9 30 C 6.2267316 30 4 27.773268 4 25 C 4 22.226732 6.2267316 20 9 20 z M 41 36 C 43.773268 36 46 38.226732 46 41 C 46 43.773268 43.773268 46 41 46 C 38.226732 46 36 43.773268 36 41 C 36 38.226732 38.226732 36 41 36 z">
                        </path>
                    </svg>
                    API Tokens
                    </Link>

                    <Link :href="route('profile.show')" @click="closeSidebar"
                        class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                    </Link>

                    <form @submit.prevent="logout">
                        <button type="submit" @click="closeSidebar"
                            class="w-full flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Mobile menu button -->
    <button @click="toggleSidebar"
        class="lg:hidden fixed top-5 right-4 z-50 p-2 rounded-md text-gray-400 hover:text-gray-500">
        <svg v-if="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</template>
