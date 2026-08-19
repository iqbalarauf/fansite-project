<x-layouts::app :title="__('Pages')">
    <div class="admin-page mx-auto w-full max-w-7xl">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl">{{ __('Pages') }}</flux:heading>
                <flux:subheading>{{ __('Kelola halaman custom yang telah dibuat.') }}</flux:subheading>
            </div>
            <flux:button :href="route('pages.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('Tambah Halaman Baru') }}
            </flux:button>
        </div>

        <div class="admin-table-shell">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="admin-table-header">{{ __('JUDUL') }}</th>
                            <th class="admin-table-header">{{ __('SLUG') }}</th>
                            <th class="admin-table-header">{{ __('STATUS') }}</th>
                            <th class="admin-table-header">{{ __('DIPERBARUI') }}</th>
                            <th class="admin-table-header text-right">{{ __('ACTIONS') }}</th>
                            <th class="admin-table-header text-right">{{ __('PUBLIC') }}</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                    @forelse ($pages as $page)
                        <tr class="admin-table-row" wire:key="page-{{ $page->id }}">
                            <td class="admin-table-cell font-medium text-zinc-800 dark:text-zinc-200">{{ $page->title }}</td>
                            <td class="admin-table-cell text-zinc-500 dark:text-zinc-400">{{ $page->slug }}</td>
                            <td class="admin-table-cell">
                                <flux:badge :color="$page->status === 'published' ? 'green' : 'zinc'">{{ $page->status }}</flux:badge>
                            </td>
                            <td class="admin-table-cell">{{ $page->updated_at?->format('d M Y H:i') }}</td>
                            <td class="admin-table-cell text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button :href="route('pages.edit', $page)" icon="pencil-square" size="sm" square :aria-label="__('Edit page')" wire:navigate />
                                    <form method="POST" action="{{ route('pages.destroy', $page) }}" onsubmit="return confirm('{{ __('Delete this page?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" variant="danger" icon="trash" size="sm" square :aria-label="__('Delete page')" />
                                    </form>
                                </div>
                            </td>
                            <td class="admin-table-cell text-right">
                                @if ($page->status === 'published')
                                    <flux:button :href="route('custom-pages.show', $page)" target="_blank" icon="arrow-top-right-on-square" size="sm" square :aria-label="__('Buka halaman publik')" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                <div class="flex flex-col items-center gap-3">
                                    <flux:icon name="rectangle-stack" class="size-10 text-zinc-300 dark:text-zinc-600" />
                                    <p class="font-medium">{{ __('Belum ada halaman yang dibuat.') }}</p>
                                    <p class="text-xs">{{ __('Tambah halaman baru dari tombol di kanan atas.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
