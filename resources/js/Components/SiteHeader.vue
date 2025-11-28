<script setup>
import { Link } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
  canLogin: { type: Boolean, default: true },
  canRegister: { type: Boolean, default: true },
});
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
      <ThemeToggle variant="desktop" />
    </nav>
  </header>
</template>
