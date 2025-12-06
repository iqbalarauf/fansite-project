<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

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

const openDropdown = ref(null);

const toggleDropdown = (name) => {
    openDropdown.value = openDropdown.value === name ? null : name;
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
            name: 'Basic Features',
            isLabel: true,
        },
        {
            name: 'My Posts',
            href: route('posts.manage'),
            icon: 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10',
            active: route().current('posts.manage') || route().current('posts.create') || route().current('posts.edit'),
        },
        {
            name: 'Pages',
            href: route('pages.index'),
            icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
            active: route().current('pages.index') || route().current('pages.create') || route().current('pages.edit'),
        },
        {
            name: 'Accounts',
            href: route('accounts.manage'),
            icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
            active: route().current('accounts.manage'),
        },
        {
            name: 'About',
            href: route('about.settings'),
            icon: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
            active: route().current('about.*'),
        },
        {
            name: 'Master Data',
            isLabel: true,
        },
        {
            name: 'Show Teater',
            icon: 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125',
            href: route('show-teater.index'),
            active: route().current('show-teater.index'),
        },
        {
            name: 'Setlist and Unit Songs',
            icon: 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-4.125',
            href: route('show-teater.categories.index'),
            active: route().current('show-teater.categories.*'),
        },
        {
            name: 'Concert Events',
            icon: 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125',
            href: route('concert-events.index'),
            active: route().current('concert-events.*'),
        },
        {
            name: 'Meet & Greet',
            icon: 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125',
            href: route('meet-greet.index'),
            active: route().current('meet-greet.*'),
        },
        {
            name: 'Live Streaming',
            icon: 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z',
            href: route('live-streaming.index'),
            active: route().current('live-streaming.*'),
        }
    ];

    return items;
});
</script>

<template>
    <!-- Sidebar -->
    <aside :class="[
        'fixed left-0 top-0 bottom-0 z-30 w-128 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
        <div class="flex flex-col h-full">
            <!-- Sidebar Header - Sticky at top -->
            <div class="flex items-center justify-between h-20 px-4 gap-2 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0">
                <Link :href="route('dashboard')" class="flex items-center min-w-0 flex-1" @click="closeSidebar">
                <img v-if="$page.props.appSettings?.app_logo" :src="$page.props.appSettings.app_logo"
                    class="h-12 w-auto lg:h-16 object-contain shrink-0" alt="logo" />
                <img v-else src="/storage/logo.svg" class="h-12 w-auto lg:h-16 object-contain shrink-0" alt="default logo" />
                <span class="text-xl font-semibold text-gray-900 dark:text-white ms-3 truncate">
                    {{ $page.props.appSettings?.sidebar_name || $page.props.appSettings?.app_name || 'Dashboard' }}
                </span>
                </Link>

                <div class="flex items-center gap-2 shrink-0">
                    <button @click="closeSidebar"
                        class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navigation Menu - Scrollable -->
            <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
                <template v-for="item in menuItems" :key="item.name">
                    <!-- Label/Header (non-clickable) -->
                    <div v-if="item.isLabel" class="px-4 py-2 mt-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ item.name }}
                    </div>

                    <!-- Menu item with dropdown -->
                    <div v-else-if="item.hasDropdown">
                        <button @click="toggleDropdown(item.name)" :class="[
                            'w-full flex items-center justify-between px-4 py-3 text-lg font-medium rounded-lg transition-colors',
                            item.active
                                ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
                        ]">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                {{ item.name }}
                            </div>
                            <svg :class="[
                                'w-4 h-4 transition-transform',
                                openDropdown === item.name ? 'rotate-180' : ''
                            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-show="openDropdown === item.name" class="mt-1 ml-8 space-y-1">
                            <Link v-for="submenu in item.submenu" :key="submenu.name" :href="submenu.href" @click="closeSidebar" :class="[
                                'block px-4 py-2 text-sm rounded-lg transition-colors',
                                submenu.active
                                    ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
                            ]">
                            {{ submenu.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- Regular menu item -->
                    <Link v-else :href="item.href" @click="closeSidebar" :class="[
                        'flex items-center px-4 py-3 text-lg font-medium rounded-lg transition-colors',
                        item.active
                            ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
                    ]">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    {{ item.name }}
                    </Link>
                </template>
            </nav>
        </div>
    </aside>

    <!-- Mobile menu button -->
    <button @click="toggleSidebar"
        class="lg:hidden fixed top-6 left-4 z-50 p-2 rounded-md text-gray-400 hover:text-gray-500">
        <svg v-if="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</template>
