<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
  canLogin: { type: Boolean, default: true },
  canRegister: { type: Boolean, default: true },
});

const aboutDropdownOpen = ref(false);

const toggleAboutDropdown = () => {
  aboutDropdownOpen.value = !aboutDropdownOpen.value;
};

const closeAboutDropdown = () => {
  aboutDropdownOpen.value = false;
};
</script>

<template>
  <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
    <Link href="/" class="flex lg:justify-left lg:col-start-1">
    <img v-if="$page.props.appSettings?.app_logo" :src="$page.props.appSettings.app_logo"
      class="h-12 w-auto lg:h-16 object-contain" alt="logo" />
    <template v-else>
      <img src="/storage/logo.svg" class="h-12 w-auto lg:h-16 object-contain" alt="default logo" />
    </template>
    </Link>

    <nav class="-mx-3 flex items-center gap-2 justify-end justify-self-end lg:col-start-3">
      <Link v-if="$page.props.auth.user" :href="route('dashboard')"
        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
      Dashboard</Link>
      <Link :href="route('blog.index')"
        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
      Blog</Link>

      <!-- About Dropdown -->
      <div class="relative" @mouseleave="closeAboutDropdown">
        <button
          @click="toggleAboutDropdown"
          @mouseenter="aboutDropdownOpen = true"
          class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white inline-flex items-center gap-1">
          About
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <div v-show="aboutDropdownOpen" class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50">
          <div class="py-1">
            <Link :href="$page.props.aboutSettings?.idol_slug ? route('about.idol', { slug: $page.props.aboutSettings.idol_slug }) : route('about.idol')" @click="closeAboutDropdown"
              class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
              {{ $page.props.aboutSettings?.idol_name || 'About Idol' }}
            </Link>
            <Link :href="$page.props.aboutSettings?.fanbase_slug ? route('about.fanbase', { slug: $page.props.aboutSettings.fanbase_slug }) : route('about.fanbase')" @click="closeAboutDropdown"
              class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
              {{ $page.props.aboutSettings?.fanbase_name || 'About Fanbase' }}
            </Link>
          </div>
        </div>
      </div>

      <Link
        v-for="page in $page.props.menuPages"
        :key="page.slug"
        :href="`/${page.slug}`"
        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
      {{ page.title }}</Link>
      <ThemeToggle variant="desktop" />
    </nav>
  </header>
</template>
