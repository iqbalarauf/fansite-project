<script setup>
defineProps({ src: String })
</script>

<template>
    <div>
        <img v-if="$page.props.appSettings?.app_logo" :src="$page.props.appSettings.app_logo"
            class="h-12 w-auto lg:h-16 object-contain" alt="logo" />
        <template v-else>
            <img src="/storage/logo.svg" class="h-12 w-auto lg:h-16 object-contain" alt="default logo" />
        </template>
    </div>
</template>
