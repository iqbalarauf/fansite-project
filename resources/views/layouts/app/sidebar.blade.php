<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gray-50 text-gray-800 antialiased dark:bg-gray-900 dark:text-gray-200">
        @php
            $groupHeading = 'px-3 pb-1 pt-2 text-xs font-medium uppercase tracking-wide text-gray-400 in-data-flux-sidebar-collapsed-desktop:hidden';
            $groupItems = 'flex flex-col gap-0.5';
        @endphp

        <flux:sidebar sticky collapsible class="border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <flux:sidebar.header class="pt-6 pb-4">
                <x-app-logo :sidebar="true" href="{{ auth()->user()?->isContentCreator() ? route('pages.index') : route('dashboard') }}" wire:navigate class="w-full"/>
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @if (auth()->user()->canAccessMasterData())
                    <div class="mb-2">
                        <div class="{{ $groupHeading }}">{{ __('Dashboard') }}</div>
                        <div class="{{ $groupItems }}">
                            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                                {{ __('Statistik Oshimen') }}
                            </flux:sidebar.item>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="{{ $groupHeading }}">{{ __('Master Data') }}</div>
                        <div class="{{ $groupItems }}">
                            <flux:sidebar.item icon="calendar-days" :href="route('show-teater.index')" :current="request()->routeIs('show-teater.index')" wire:navigate>
                                {{ __('Show Teater') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="musical-note" :href="route('show-teater.categories.index')" :current="request()->routeIs('show-teater.categories.*')" :tooltip="__('Setlist & Unit Song')" wire:navigate>
                                {{ __('Setlist & Unit Song') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="ticket" :href="route('meet-greet-events.index')" :current="request()->routeIs('meet-greet-events.*')" :tooltip="__('Meet & Greet Events')" wire:navigate>
                                {{ __('Meet & Greet Events') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="radio" :href="route('concert-events.index')" :current="request()->routeIs('concert-events.*')" :tooltip="__('Concert & Events')" wire:navigate>
                                {{ __('Concert & Events') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="video-camera" :href="route('live-streaming.index')" :current="request()->routeIs('live-streaming.*')" wire:navigate>
                                {{ __('Live Streaming') }}
                            </flux:sidebar.item>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->canAccessPages())
                    @php
                        $__newsEnabled = \App\Support\SettingBag::featureEnabled('news');
                        $__blogEnabled = \App\Support\SettingBag::featureEnabled('blog');
                        $__triviaEnabled = \App\Support\SettingBag::featureEnabled('trivia');
                    @endphp

                    <div class="mb-2">
                        <div class="{{ $groupHeading }}">{{ __('Content Management') }}</div>
                        <div class="{{ $groupItems }}">
                            <flux:sidebar.item icon="rectangle-stack" :href="route('pages.index')" :current="request()->routeIs('pages.*')" wire:navigate>
                                {{ __('Pages') }}
                            </flux:sidebar.item>
                            @if (\App\Support\SettingBag::featureEnabled('magazines'))
                                <flux:sidebar.item icon="book-open" :href="route('magazines.index')" :current="request()->routeIs('magazines.*')" wire:navigate>
                                    {{ __('Majalah') }}
                                </flux:sidebar.item>
                            @endif
                            <flux:sidebar.item icon="photo" :href="route('content.gallery.index')" :current="request()->routeIs('content.gallery.*')" wire:navigate>
                                {{ __('Galeri') }}
                            </flux:sidebar.item>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="{{ $groupHeading }}">{{ __('Additional Content') }}</div>
                        <div class="{{ $groupItems }}">
                            @if ($__newsEnabled)
                                <flux:sidebar.item icon="newspaper" :href="route('content.news.index')" :current="request()->routeIs('content.news.*')" wire:navigate>
                                    {{ __('News') }}
                                </flux:sidebar.item>
                            @endif
                            @if ($__blogEnabled)
                                <flux:sidebar.item icon="pencil-square" :href="route('content.blog.index')" :current="request()->routeIs('content.blog.*')" wire:navigate>
                                    {{ __('Blog') }}
                                </flux:sidebar.item>
                            @endif
                            <flux:sidebar.item icon="clock" :href="route('content.timeline.index')" :current="request()->routeIs('content.timeline.*')" wire:navigate>
                                {{ __('Timeline') }}
                            </flux:sidebar.item>
                            @if ($__triviaEnabled)
                                <flux:sidebar.item icon="question-mark-circle" :href="route('content.trivia.index')" :current="request()->routeIs('content.trivia.*')" wire:navigate>
                                    {{ __('Trivia') }}
                                </flux:sidebar.item>
                            @endif
                            @if (auth()->user()?->isSuperAdmin())
                                <flux:sidebar.item icon="camera" :href="route('photobooth.edit')" :current="request()->routeIs('photobooth.edit')" wire:navigate>
                                    {{ __('Photobooth') }}
                                </flux:sidebar.item>
                            @endif
                        </div>
                    </div>
                @endif

                @if (auth()->user()->isSuperAdmin())
                    <div class="mb-2">
                        <div class="{{ $groupHeading }}">{{ __('User Management') }}</div>
                        <div class="{{ $groupItems }}">
                            <flux:sidebar.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                                {{ __('Daftar User') }}
                            </flux:sidebar.item>
                        </div>
                    </div>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/iqbalarauf/fansite-project" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://docs.google.com/document/d/1AIrr9cZ8VELNgVn832Lq155lxQPqReDTjJ9aOvZN5yI/edit?usp=sharing" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        <flux:header sticky class="min-h-16 gap-2 border-b border-gray-200 bg-white px-4 dark:border-gray-800 dark:bg-gray-900 sm:px-6 lg:px-8">
            <flux:sidebar.toggle icon="bars-2" inset="left" class="-ms-2" />

            <x-admin-search />

            <flux:spacer />

            <button
                type="button"
                x-data
                x-on:click="$flux.appearance = document.documentElement.classList.contains('dark') ? 'light' : 'dark'"
                aria-label="{{ __('Ganti mode terang/gelap') }}"
                class="relative flex size-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            >
                <flux:icon icon="sun" variant="outline" class="size-5 dark:hidden" />
                <flux:icon icon="moon" variant="outline" class="hidden size-5 dark:block" />
            </button>

            <x-header-user-menu />
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
