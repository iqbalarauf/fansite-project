<x-layouts::app :title="__('Majalah')">
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">Majalah</flux:heading>
                <flux:subheading>Kelola majalah digital, cover, deskripsi, dan statistik pembaca</flux:subheading>
            </div>
            <flux:modal.trigger name="modal-create-magazine">
                <flux:button variant="primary" icon="plus">Tambah Majalah</flux:button>
            </flux:modal.trigger>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="admin-table-shell">
            <form method="GET" action="{{ route('magazines.index') }}" id="filter-form">
                <x-admin.table-toolbar :filters="$filters" :show-filters="true" search-placeholder="Cari judul atau slug majalah...">
                    <select name="sort_by" onchange="this.form.submit()" class="admin-filter-select">
                        <option value="created_at" {{ $filters['sort_by'] === 'created_at' ? 'selected' : '' }}>{{ __('Terbaru') }}</option>
                        <option value="title" {{ $filters['sort_by'] === 'title' ? 'selected' : '' }}>{{ __('Judul') }}</option>
                        <option value="views" {{ $filters['sort_by'] === 'views' ? 'selected' : '' }}>Viewers</option>
                        <option value="downloads" {{ $filters['sort_by'] === 'downloads' ? 'selected' : '' }}>Downloads</option>
                    </select>

                    <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] }}" id="sort-dir-input" />
                    <button
                        type="button"
                        title="{{ $filters['sort_dir'] === 'asc' ? 'Ascending' : 'Descending' }}"
                        onclick="document.getElementById('sort-dir-input').value = '{{ $filters['sort_dir'] === 'asc' ? 'desc' : 'asc' }}'; document.getElementById('filter-form').submit();"
                        class="admin-filter-sort"
                    >
                        @if ($filters['sort_dir'] === 'asc')
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>
                        @endif
                    </button>

                    <flux:button type="submit" variant="outline">{{ __('Terapkan') }}</flux:button>

                    @if ($filters['search'])
                        <a href="{{ route('magazines.index') }}" class="admin-filter-reset">{{ __('Reset') }}</a>
                    @endif
                </x-admin.table-toolbar>
            </form>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th>MAJALAH</th>
                            <th>DESKRIPSI</th>
                            <th class="text-center">VIEWERS</th>
                            <th class="text-center">DOWNLOADS</th>
                            <th class="text-center">STATUS</th>
                            <th class="text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @forelse ($magazines as $magazine)
                            <tr class="admin-table-row">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-14 w-11 shrink-0 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                                            @if ($magazine->cover)
                                                <img src="{{ Storage::url($magazine->cover) }}" alt="{{ $magazine->title }}" class="h-full w-full object-cover" />
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-zinc-400 dark:text-zinc-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $magazine->title }}</p>
                                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">/majalah/{{ $magazine->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="max-w-xs px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                    <p class="line-clamp-2">{{ $magazine->description ?: '–' }}</p>
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-zinc-700 dark:text-zinc-200">{{ number_format($magazine->views) }}</td>
                                <td class="px-4 py-3 text-center font-medium text-zinc-700 dark:text-zinc-200">{{ number_format($magazine->downloads) }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($magazine->is_main)
                                        <flux:badge color="lime" size="sm">Main Magazine</flux:badge>
                                    @else
                                        <span class="text-zinc-400 dark:text-zinc-500">–</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        @unless ($magazine->is_main)
                                            <form method="POST" action="{{ route('magazines.set-main', $magazine) }}">
                                                @csrf
                                                <button type="submit" class="admin-action-link" :disabled="auth()->user()?->isViewOnly()">Set Main</button>
                                            </form>
                                        @endunless
                                        <button
                                            type="button"
                                            class="admin-action-link"
                                            data-id="{{ $magazine->id }}"
                                            data-title="{{ $magazine->title }}"
                                            data-slug="{{ $magazine->slug }}"
                                            data-description="{{ $magazine->description }}"
                                            onclick="openEditModal(this)"
                                        >
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('magazines.destroy', $magazine) }}" onsubmit="return confirm('Hapus majalah ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-danger" :disabled="auth()->user()?->isViewOnly()">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                        <p class="font-medium">Belum ada majalah</p>
                                        <p class="text-xs">Tambahkan majalah baru dari tombol di kanan atas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('show-teater.partials.pagination', ['paginator' => $magazines, 'perPage' => $filters['per_page'], 'pageParam' => 'page'])
        </div>
    </div>

    <flux:modal name="modal-create-magazine" class="md:w-[620px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Tambah Majalah</flux:heading>

            <form method="POST" action="{{ route('magazines.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <flux:label for="create-magazine-title">Judul</flux:label>
                    <flux:input id="create-magazine-title" name="title" required class="mt-1" oninput="autoSlug(this.value)" />
                </div>

                <div>
                    <flux:label for="create-magazine-slug">Slug URL</flux:label>
                    <flux:input id="create-magazine-slug" name="slug" required class="mt-1" placeholder="edisi-perdana" />
                    <flux:text class="mt-1 text-xs">Alamat publik: /majalah/<span id="slug-preview">slug</span></flux:text>
                </div>

                <div>
                    <flux:label for="create-magazine-description">Deskripsi</flux:label>
                    <flux:textarea id="create-magazine-description" name="description" rows="4" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:label for="create-magazine-cover">Cover</flux:label>
                        <input id="create-magazine-cover" type="file" name="cover" accept="image/*" class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                    </div>
                    <div>
                        <flux:label for="create-magazine-file">File PDF</flux:label>
                        <input id="create-magazine-file" type="file" name="file" accept="application/pdf" required class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                    </div>
                </div>

                <label class="flex items-center gap-2 rounded-xl border border-zinc-200 p-3 text-sm text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                    <input type="checkbox" name="is_main" value="1" class="rounded border-zinc-300 text-blue-600">
                    <span>Choose as Main Magazine</span>
                </label>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="modal-edit-magazine" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Majalah</flux:heading>

            <form method="POST" id="edit-magazine-form" action="" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <flux:label for="edit-magazine-slug">Slug URL</flux:label>
                    <flux:input id="edit-magazine-slug" name="slug" required class="mt-1" />
                </div>

                <div>
                    <flux:label for="edit-magazine-description">Deskripsi</flux:label>
                    <flux:textarea id="edit-magazine-description" name="description" rows="5" class="mt-1" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Update</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <script>
        function autoSlug(value) {
            const slugInput = document.getElementById('create-magazine-slug');
            const preview = document.getElementById('slug-preview');

            if (slugInput.dataset.edited === 'true') {
                return;
            }

            const slug = value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');

            slugInput.value = slug;
            preview.textContent = slug || 'slug';
        }

        function openEditModal(target) {
            document.getElementById('edit-magazine-form').action = `{{ url('magazines') }}/${target.dataset.id}`;
            document.getElementById('edit-magazine-slug').value = target.dataset.slug || '';
            document.getElementById('edit-magazine-description').value = target.dataset.description || '';

            Flux.modal('modal-edit-magazine').show();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const slugInput = document.getElementById('create-magazine-slug');
            const preview = document.getElementById('slug-preview');

            slugInput.addEventListener('input', () => {
                slugInput.dataset.edited = 'true';
                preview.textContent = slugInput.value || 'slug';
            });
        });
    </script>
</x-layouts::app>
