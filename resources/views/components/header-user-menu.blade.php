<flux:dropdown position="bottom" align="end">
    <button type="button" class="flex items-center gap-2 rounded-full p-1 pe-2 transition hover:bg-gray-100 dark:hover:bg-gray-800" data-test="header-menu-button">
        <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" class="size-9" />
        <span class="hidden text-sm font-medium text-gray-700 dark:text-gray-200 sm:block">{{ auth()->user()->name }}</span>
        <flux:icon icon="chevron-down" variant="micro" class="hidden size-4 text-gray-400 sm:block" />
    </button>

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>

        <flux:menu.separator />

        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>
                {{ __('Profile') }}
            </flux:menu.item>
            @unless (auth()->user()?->isViewOnly())
                <flux:menu.item :href="route('appearance.edit')" icon="cog" wire:navigate>
                    {{ __('Settings') }}
                </flux:menu.item>
            @endunless
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer"
                    data-test="logout-button"
                >
                    {{ __('Log out') }}
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
