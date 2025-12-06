<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import Dropdown from './Dropdown.vue';
import DropdownLink from './DropdownLink.vue';

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
      <Link :href="route('public.gallery')"
        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
      Gallery</Link>

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

        <div v-show="aboutDropdownOpen" class="absolute right-0 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50">
          <div class="py-1">
            <DropdownLink :href="$page.props.aboutSettings?.idol_slug ? route('about.idol', { slug: $page.props.aboutSettings.idol_slug }) : route('about.idol')" @click="closeAboutDropdown"
              class="block px-4 py-2 text-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
              {{ $page.props.aboutSettings?.idol_name || 'About Idol' }}
            </DropdownLink>
            <DropdownLink :href="$page.props.aboutSettings?.fanbase_slug ? route('about.fanbase', { slug: $page.props.aboutSettings.fanbase_slug }) : route('about.fanbase')" @click="closeAboutDropdown"
              class="block px-4 py-2 text-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
              {{ $page.props.aboutSettings?.fanbase_name || 'About Fanbase' }}
            </DropdownLink>
          </div>
        </div>
      </div>

      <Link
        v-for="page in $page.props.menuPages"
        :key="page.slug"
        :href="`/${page.slug}`"
        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
      {{ page.title }}</Link>

      <ThemeToggle variant="desktop" class="border-r"/>

      <!-- Social Media Icons -->
      <a v-if="$page.props.appSettings?.instagram_url"
         :href="$page.props.appSettings.instagram_url"
         target="_blank"
         rel="noopener noreferrer"
         class="rounded-md p-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
         title="Instagram">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
        </svg>
      </a>

      <a v-if="$page.props.appSettings?.twitter_url"
         :href="$page.props.appSettings.twitter_url"
         target="_blank"
         rel="noopener noreferrer"
         class="rounded-md p-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
         title="Twitter/X">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
          <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
        </svg>
      </a>

      <a v-if="$page.props.appSettings?.tiktok_url"
         :href="$page.props.appSettings.tiktok_url"
         target="_blank"
         rel="noopener noreferrer"
         class="rounded-md p-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
         title="TikTok">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
          <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
        </svg>
      </a>
    </nav>
  </header>
</template>
