<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>

    <x-alert-modal />
</x-layouts::app.sidebar>
