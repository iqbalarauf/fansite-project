<x-layouts::app :title="__('Merchandise')">
    <div class="admin-page mx-auto w-full max-w-7xl">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl">{{ __('Merchandise') }}</flux:heading>
                <flux:subheading>{{ __('Kelola katalog produk merchandise.') }}</flux:subheading>
            </div>
            <div class="admin-page-actions">
                <flux:modal.trigger name="modal-create-merchandise">
                    <flux:button variant="primary" icon="plus">{{ __('Tambah Produk') }}</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="admin-table-shell">
            <form method="GET" action="{{ route('merchandise.admin.index') }}">
                <x-admin.table-toolbar :filters="$filters" search-placeholder="Cari nama atau slug produk..." />
            </form>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="admin-table-header">{{ __('PRODUK') }}</th>
                            <th class="admin-table-header">{{ __('HARGA') }}</th>
                            <th class="admin-table-header">{{ __('FOTO') }}</th>
                            <th class="admin-table-header">{{ __('STATUS') }}</th>
                            <th class="admin-table-header">{{ __('URUTAN') }}</th>
                            <th class="admin-table-header text-right">{{ __('ACTIONS') }}</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                    @forelse ($products as $product)
                        <tr class="admin-table-row" wire:key="product-{{ $product->id }}">
                            <td class="admin-table-cell">
                                <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $product->name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->slug }}</p>
                            </td>
                            <td class="admin-table-cell text-zinc-500 dark:text-zinc-400">
                                {{ $product->price !== null ? 'Rp '.number_format((float) $product->price, 0, ',', '.') : '–' }}
                            </td>
                            <td class="admin-table-cell text-zinc-500 dark:text-zinc-400">{{ count($product->imagePaths()) }}</td>
                            <td class="admin-table-cell">
                                <flux:badge :color="$product->is_active ? 'green' : 'zinc'">{{ $product->is_active ? __('Aktif') : __('Nonaktif') }}</flux:badge>
                            </td>
                            <td class="admin-table-cell text-zinc-500 dark:text-zinc-400">{{ $product->sort_order }}</td>
                            <td class="admin-table-cell text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($product->is_active)
                                        <flux:button :href="route('merchandise.show', $product)" target="_blank" icon="arrow-top-right-on-square" size="sm" square :aria-label="__('Lihat di publik')" />
                                    @endif
                                    <flux:modal.trigger name="modal-edit-merchandise-{{ $product->id }}">
                                        <flux:button icon="pencil-square" size="sm" square :aria-label="__('Edit produk')" />
                                    </flux:modal.trigger>
                                    <form method="POST" action="{{ route('merchandise.admin.destroy', $product) }}"
                                          x-data
                                          data-confirm-message="{{ __('Hapus produk ini?') }}"
                                          data-confirm-label="{{ __('Hapus') }}"
                                          x-on:submit.prevent="window.appConfirm($el.dataset.confirmMessage, { variant: 'error', confirmText: $el.dataset.confirmLabel }).then(ok => ok && $el.submit())">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" variant="danger" icon="trash" size="sm" square :aria-label="__('Hapus produk')" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                <div class="flex flex-col items-center gap-3">
                                    <flux:icon name="shopping-bag" class="size-10 text-zinc-300 dark:text-zinc-600" />
                                    <p class="font-medium">{{ __('Belum ada produk merchandise.') }}</p>
                                    <p class="text-xs">{{ __('Tambah produk dari tombol di kanan atas.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @include('show-teater.partials.pagination', ['paginator' => $products, 'perPage' => $filters['per_page'], 'pageParam' => 'page'])
        </div>
    </div>

    {{-- Create Modal --}}
    <flux:modal name="modal-create-merchandise" class="md:w-[600px]">
        <flux:heading size="lg">{{ __('Tambah Produk') }}</flux:heading>

        <form method="POST" action="{{ route('merchandise.admin.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
            @csrf
            <flux:input name="name" :label="__('Nama Produk')" required />
            <flux:input name="slug" :label="__('Slug (opsional)')" placeholder="otomatis dari nama" />
            <flux:textarea name="description" :label="__('Deskripsi')" rows="4" />
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input name="price" type="number" step="0.01" min="0" :label="__('Harga (opsional)')" />
                <flux:input name="sort_order" type="number" min="0" value="0" :label="__('Urutan')" />
            </div>
            <flux:input name="shop_url" type="url" :label="__('Link Belanja (opsional)')" placeholder="https://..." />
            <div class="space-y-2">
                <flux:label>{{ __('Foto Produk (maks. 8)') }}</flux:label>
                <input type="file" name="images[]" accept="image/*" multiple class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                @error('images.*') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <label class="inline-flex items-center gap-2 text-sm font-medium">
                <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-zinc-300 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800">
                {{ __('Tampilkan di publik') }}
            </label>

            <div class="flex items-center justify-end gap-3 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Modals --}}
    @foreach ($products as $product)
        <flux:modal name="modal-edit-merchandise-{{ $product->id }}" class="md:w-[600px]">
            <flux:heading size="lg">{{ __('Edit Produk') }}</flux:heading>

            <form method="POST" action="{{ route('merchandise.admin.update', $product) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <flux:input name="name" :label="__('Nama Produk')" :value="$product->name" required />
                <flux:input name="slug" :label="__('Slug')" :value="$product->slug" />
                <flux:textarea name="description" :label="__('Deskripsi')" rows="4">{{ $product->description }}</flux:textarea>
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input name="price" type="number" step="0.01" min="0" :label="__('Harga (opsional)')" :value="$product->price" />
                    <flux:input name="sort_order" type="number" min="0" :label="__('Urutan')" :value="$product->sort_order" />
                </div>
                <flux:input name="shop_url" type="url" :label="__('Link Belanja (opsional)')" :value="$product->shop_url" />

                @if (count($product->imagePaths()) > 0)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach ($product->imageUrls() as $url)
                            <img src="{{ $url }}" alt="" class="aspect-square w-full rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                        @endforeach
                    </div>
                @endif

                <div class="space-y-2">
                    <flux:label>{{ __('Tambah Foto (maks. 8, foto lama tetap)') }}</flux:label>
                    <input type="file" name="images[]" accept="image/*" multiple class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                    @error('images.*') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <label class="inline-flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" name="is_active" value="1" @checked($product->is_active) class="h-4 w-4 rounded border-zinc-300 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800">
                    {{ __('Tampilkan di publik') }}
                </label>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
                </div>
            </form>
        </flux:modal>
    @endforeach
</x-layouts::app>
