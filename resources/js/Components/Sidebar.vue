<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const isOpen = ref(false);
const isCollapsed = ref(false);

// Persist collapsed state
onMounted(() => {
    try {
        const stored = localStorage.getItem('sidebar-collapsed');
        if (stored !== null) isCollapsed.value = stored === '1';
    } catch (e) { }
});

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
    try {
        localStorage.setItem('sidebar-collapsed', isCollapsed.value ? '1' : '0');
    } catch (e) { }
    // Emit event for AppLayout to adjust main content padding
    window.dispatchEvent(new CustomEvent('sidebar-collapse', { detail: isCollapsed.value }));
};

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
            name: 'Gallery',
            icon: 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
            href: route('gallery.index'),
            active: route().current('gallery.*'),
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
        },
        {
            name: 'Special Features',
            isLabel: true,
        },
        {
            name: 'Online Photobooth',
            icon: 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z',
        },
        {
            name: '2-shot Online',
            icon: 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z',
        },
        {
            name: 'Settings',
            isLabel: true,
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
    ];

    return items;
});
</script>

<template>
    <!-- Sidebar -->
    <aside :class="[
        'fixed left-0 top-0 bottom-0 z-30 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-all duration-300 ease-in-out',
        isCollapsed ? 'lg:w-16' : 'lg:w-64',
        'w-64',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
        <div class="flex flex-col h-full">
            <!-- Sidebar Header - Sticky at top -->
            <div
                class="relative flex items-center justify-between h-20 px-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0 transition-all duration-300">
                <!-- Logo + App Name -->
                <Link :href="route('dashboard')"
                    :class="['flex items-center min-w-0 overflow-hidden transition-all duration-300', isCollapsed ? 'lg:mx-auto' : '']"
                    @click="closeSidebar">
                    <img v-if="$page.props.appSettings?.app_logo" :src="$page.props.appSettings.app_logo"
                        class="h-10 w-auto object-contain shrink-0" alt="logo" />
                    <img v-else src="/storage/logo.svg" class="h-10 w-auto object-contain shrink-0"
                        alt="default logo" />
                    <span
                        :class="['text-base font-semibold text-gray-900 dark:text-white ms-2 truncate transition-all duration-300', isCollapsed ? 'lg:opacity-0 lg:w-0 lg:ms-0 lg:overflow-hidden opacity-100 w-auto' : 'opacity-100']">
                        {{ $page.props.appSettings?.sidebar_name || $page.props.appSettings?.app_name || 'Dashboard' }}
                    </span>
                </Link>

                <!-- Collapse button (desktop) + Close button (mobile) -->
                <!-- Toggle collapse button — always visible on desktop -->
                <button @click="toggleCollapse"
                    class="hidden lg:flex items-center justify-center w-8 h-8 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm transition-all duration-300 absolute -right-4 top-6 z-50"
                    :title="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                    <!-- Chevron left when expanded, chevron right when collapsed -->
                    <svg class="w-5 h-5 transition-transform duration-300" :class="isCollapsed ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Close button on mobile -->
                <button @click="closeSidebar"
                    class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    title="Close sidebar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Menu - Scrollable -->
            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto hide-scrollbar">
                <template v-for="item in menuItems" :key="item.name">
                    <!-- Label/Header (non-clickable) - hidden when collapsed -->
                    <div v-if="item.isLabel"
                        :class="['px-3 py-2 mt-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider transition-all duration-300 overflow-hidden', isCollapsed ? 'lg:opacity-0 lg:h-0 lg:py-0 lg:mt-0 opacity-100' : 'opacity-100']">
                        {{ item.name }}
                    </div>

                    <!-- Regular menu item -->
                    <Link v-else :href="item.href" @click="closeSidebar" :class="[
                        'flex items-center py-2.5 text-sm font-medium rounded-lg transition-colors group',
                        isCollapsed ? 'lg:justify-center lg:px-2 px-3' : 'px-3',
                        item.active
                            ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'
                    ]" :title="isCollapsed ? item.name : ''">
                        <svg class="w-5 h-5 shrink-0" :class="isCollapsed ? 'lg:mr-0 mr-3' : 'mr-3'" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                        </svg>
                        <span
                            :class="['truncate transition-all duration-300 overflow-hidden whitespace-nowrap', isCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden opacity-100 w-auto' : 'opacity-100']">
                            {{ item.name }}
                        </span>
                    </Link>
                </template>
            </nav>
        </div>
    </aside>

    <!-- Mobile menu button (only visible when sidebar is closed) -->
    <button v-if="!isOpen" @click="toggleSidebar"
        class="lg:hidden fixed top-6 left-4 z-50 p-2 rounded-md text-gray-400 hover:text-gray-500 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.hide-scrollbar {
    scrollbar-width: none;
}
</style>
