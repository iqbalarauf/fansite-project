<x-layouts::auth.login :title="__('Log in')">
    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-black text-slate-900 dark:text-white sm:text-3xl">{{ __('Masuk ke Akun Anda') }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Masukkan email dan password untuk melanjutkan.') }}</p>
    </div>

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
        @csrf

        <!-- Email Address -->
        <flux:input
            name="email"
            :label="__('Email address')"
            :value="old('email')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

        <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
            {{ __('Log in') }}
        </flux:button>
    </form>
</x-layouts::auth.login>
