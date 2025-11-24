<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'desktop', // 'desktop' or 'mobile'
  },
});

const isDark = ref(false);

function applyDark(value) {
  const html = document.documentElement;
  if (value) html.classList.add('dark'); else html.classList.remove('dark');
  try {
    localStorage.setItem('dark-mode', value ? '1' : '0');
  } catch (e) {
    // ignore
  }
  isDark.value = !!value;
}

function toggleDark() {
  applyDark(!isDark.value);
}

onMounted(() => {
  try {
    const stored = localStorage.getItem('dark-mode');
    if (stored !== null) {
      applyDark(stored === '1');
    } else {
      isDark.value = document.documentElement.classList.contains('dark');
    }
  } catch (e) {
    // ignore
  }
});
</script>

<template>
  <div v-if="variant === 'desktop'" class="me-3">
    <button type="button" @click="toggleDark" title="Toggle dark mode" aria-label="Toggle dark mode" class="relative inline-flex items-center p-4 rounded-md text-gray-500 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
      <!-- Sun icon (visible in dark mode) -->
      <span v-show="isDark" class="absolute inset-0 flex items-center justify-center transition-transform duration-300" :class="isDark ? 'opacity-100 scale-100 rotate-0' : 'opacity-0 scale-75 -rotate-12'">
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36-6.36l-1.41 1.41M7.05 16.95l-1.41 1.41M18.36 18.36l-1.41-1.41M7.05 7.05L5.64 5.64M12 8a4 4 0 100 8 4 4 0 000-8z" />
        </svg>
      </span>

      <!-- Moon icon (visible in light mode) -->
      <span v-show="!isDark" class="absolute inset-0 flex items-center justify-center transition-transform duration-300" :class="isDark ? 'opacity-0 scale-75 rotate-12' : 'opacity-100 scale-100 rotate-0'">
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
        </svg>
      </span>
    </button>
  </div>

  <div v-else class="me-3">
    <button type="button" @click="toggleDark" title="Toggle dark mode" aria-label="Toggle dark mode" class="inline-flex items-center p-2 rounded-md text-gray-500 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
      <span v-show="isDark" class="transition-transform duration-300" :class="isDark ? 'opacity-100 scale-100' : 'opacity-0 scale-75'"><svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36-6.36l-1.41 1.41M7.05 16.95l-1.41 1.41M18.36 18.36l-1.41-1.41M7.05 7.05L5.64 5.64M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg></span>
      <span v-show="!isDark" class="transition-transform duration-300" :class="isDark ? 'opacity-0 scale-75' : 'opacity-100 scale-100'"><svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg></span>
    </button>
  </div>
</template>
