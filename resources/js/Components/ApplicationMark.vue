<script setup>
defineProps({ src: String })
</script>

<template>
    <div>
        <img v-if="$page.props.appSettings?.logo" :src="$page.props.appSettings.logo"
            class="h-12 w-auto lg:h-16 object-contain" alt="logo" />
        <template v-else>
            <img src="/storage/logo.svg" class="h-12 w-auto lg:h-16 object-contain" alt="default logo" />
        </template>
    </div>
</template>
