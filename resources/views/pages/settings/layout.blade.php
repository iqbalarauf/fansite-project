@php
    $tabs = [
        ['label' => __('Appearance'), 'route' => route('appearance.edit'), 'active' => request()->routeIs('appearance.edit')],
    ];

    if (auth()->user()?->isSuperAdmin()) {
        $tabs[] = ['label' => __('Features Activation'), 'route' => route('features.edit'), 'active' => request()->routeIs('features.edit')];
        $tabs[] = ['label' => __('Header Menu'), 'route' => route('header-menu.edit'), 'active' => request()->routeIs('header-menu.edit')];
    }
@endphp

<div class="w-full">
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex flex-wrap" aria-label="{{ __('Settings') }}">
            @foreach ($tabs as $tab)
                <a
                    href="{{ $tab['route'] }}"
                    wire:navigate
                    @if ($tab['active']) aria-current="page" @endif
                    class="inline-flex items-center whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition {{ $tab['active']
                        ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                        : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' }}"
                >
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    <div class="mt-6 w-full">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full">
            {{ $slot }}
        </div>
    </div>
</div>
