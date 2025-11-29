<template>
  <Head :title="page.title" />

  <div class="bg-gray-100 dark:bg-gray-900 text-black/50 dark:text-white/50">
    <img id="background" class="absolute -left-20 top-0 max-w-[877px]" src="https://laravel.com/assets/img/welcome/background.svg" />
    
    <div class="min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
      <!-- Header -->
      <div class="sticky top-0 z-50 left-0 right-0 w-full bg-gray-100 dark:bg-gray-900 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-6">
          <SiteHeader :can-login="$page.props.auth?.user != null" :can-register="false" />
        </div>
      </div>

      <!-- Hero Section (16:9 aspect ratio) -->
      <section class="mb-8 w-full relative z-0">
        <div class="relative w-full overflow-hidden">
          <!-- Mobile: fixed height, Medium+: 16:9 aspect ratio -->
          <div class="w-full h-[500px] md:h-0 md:pb-[56.25%] relative">
            <img 
              :src="page.hero_image || '/storage/hero.jpg'" 
              alt="Hero background" 
              class="absolute inset-0 w-full h-full object-cover object-top" 
            />
            <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/40"></div>

            <div class="absolute inset-0 flex items-center justify-start">
              <div class="max-w-7xl mx-auto w-full px-4 sm:px-6">
                <div class="max-w-3xl text-left py-8 sm:py-12 text-white sm:pl-6 md:pl-12">
                  <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold leading-tight">{{ page.title }}</h1>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Page Content Body -->
      <section v-if="page.body" class="w-full max-w-4xl mx-auto px-4 sm:px-6 py-12">
        <div class="prose prose-lg dark:prose-invert max-w-none" v-html="page.body"></div>
      </section>

      <!-- Call-to-Action Section (optional) -->
      <section v-if="page.has_cta_section" class="w-full py-12 relative z-0">
        <div 
          class="relative w-full min-h-[400px] flex items-center justify-center"
          :style="{ backgroundImage: page.cta_bg_image ? `url(${page.cta_bg_image})` : 'none', backgroundColor: page.cta_bg_image ? 'transparent' : '#1f2937' }"
          :class="{ 'bg-cover bg-center': page.cta_bg_image }"
        >
          <!-- Dark overlay for better text readability -->
          <div class="absolute inset-0 bg-black/50"></div>
          
          <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 text-center text-white">
            <h2 v-if="page.cta_title" class="text-3xl sm:text-4xl md:text-5xl font-bold mb-6">
              {{ page.cta_title }}
            </h2>
            
            <p v-if="page.cta_description" class="text-lg sm:text-xl mb-8 leading-relaxed">
              {{ page.cta_description }}
            </p>
            
            <a 
              v-if="page.cta_button_text && page.cta_button_url"
              :href="page.cta_button_url"
              class="inline-flex items-center justify-center px-8 py-4 bg-white text-gray-900 font-semibold text-lg rounded-lg hover:bg-gray-100 transition shadow-lg"
            >
              {{ page.cta_button_text }}
            </a>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <Footer type="public" />
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
  page: Object,
});
</script>
