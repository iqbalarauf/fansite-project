<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Log in" />
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
        <div class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900">
            <div class="flex flex-col flex-1 w-full lg:w-1/2">
                <div class="w-full max-w-md pt-10 mx-auto">
                    <Link href="/"
                        class="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 20 20" fill="none">
                        <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Back to website
                    </Link>
                </div>
                <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                    <div>
                        <div class="flex flex-col items-start max-w-xs mb-2 sm:mb-4">
                            <img v-if="$page.props.appSettings?.logo" :src="$page.props.appSettings.logo"
                                class="h-12 w-auto lg:h-16 object-contain" alt="logo" />
                            <template v-else>
                                <img src="/storage/logo.svg" class="h-36 w-auto lg:h-48 object-contain"
                                    alt="default logo" />
                            </template>
                        </div>
                        <div class="mb-5 sm:mb-8">
                            <h1
                                class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                                Sign In
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Enter your email and password to sign in!
                            </p>
                        </div>
                        <div>
                            <form @submit.prevent="submit">
                                <div class="space-y-5">
                                    <!-- Email -->
                                    <div>
                                        <InputLabel for="email" value="Email" />
                                        <TextInput id="email" v-model="form.email" type="email"
                                            class="mt-1 block w-full" required autofocus autocomplete="username" />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>
                                    <!-- Password -->
                                    <div>
                                        <InputLabel for="password" value="Password" />
                                        <TextInput id="password" v-model="form.password" type="password"
                                            class="mt-1 block w-full" required autocomplete="current-password" />
                                        <InputError class="mt-2" :message="form.errors.password" />
                                    </div>
                                    <!-- Checkbox -->
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="flex items-center">
                                                <Checkbox v-model:checked="form.remember" name="remember" />
                                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember
                                                    me</span>
                                            </label>
                                        </div>
                                        <Link v-if="canResetPassword" :href="route('password.request')"
                                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                        Forgot your password?
                                        </Link>
                                    </div>
                                    <!-- Button -->
                                    <div>
                                        <PrimaryButton
                                            class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"
                                            :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            Log in
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-5">
                                <p
                                    class="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
                                    Don't have an account? Contact the administrator.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative items-center hidden w-full h-full lg:w-1/2 lg:grid bg-cover bg-center"
                :style="{ backgroundImage: `url(${$page.props.appSettings?.login_image ?? '/storage/hero_2.jpg'})` }">
                <!-- overlay to ensure text/illustration stays readable on top of the photo -->
                <div class="absolute inset-0 bg-black/30 dark:bg-black/60"></div>
                <div class="relative flex items-center justify-center z-10">
                    <common-grid-shape />
                </div>
            </div>
        </div>
    </div>
</template>

<!--  
<AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                    Forgot your password?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
-->