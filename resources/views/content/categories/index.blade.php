<x-layouts::app :title="__('Kategori')">
    <div class="admin-page mx-auto w-full max-w-4xl">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">Kategori</flux:heading>
                <flux:subheading>Kelola kategori untuk News dan Blog.</flux:subheading>
            </div>
            <flux:modal.trigger name="modal-create-category">
                <flux:button variant="primary" icon="plus">Tambah Kategori</flux:button>
            </flux:modal.trigger>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-2 rounded-lg border border-zinc-200 p-1 dark:border-zinc-700">
            @foreach (\App\Enums\ContentSection::cases() as $case)
                <a href="{{ route('content.categories.index', ['type' => $case->value]) }}"
                   class="rounded-md px-4 py-2 text-sm font-medium transition {{ $type === $case ? 'bg-blue-600 text-white' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                    {{ $case->label() }}
                </a>
            @endforeach
        </div>

        <div class="admin-table-shell">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th>NAMA</th>
                            <th>SLUG</th>
                            <th class="text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @forelse ($categories as $category)
                            <tr class="admin-table-row">
                                <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">{{ $category->name }}</td>
                                <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $category->slug }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" class="admin-action-link"
                                                data-id="{{ $category->id }}"
                                                data-name="{{ $category->name }}"
                                                data-slug="{{ $category->slug }}"
                                                onclick="openCategoryEdit(this)">Edit</button>
                                        <form method="POST" action="{{ route('content.categories.destroy', $category->id) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-danger" :disabled="auth()->user()?->isViewOnly()">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-12 text-center text-zinc-500 dark:text-zinc-400">Belum ada kategori {{ $type->label() }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <flux:modal name="modal-create-category" class="md:w-[480px]">
        <div class="space-y-6">
            <flux:heading size="lg">Tambah Kategori {{ $type->label() }}</flux:heading>
            <form method="POST" action="{{ route('content.categories.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="type" value="{{ $type->value }}" />
                <div>
                    <flux:label for="category-name">Nama</flux:label>
                    <flux:input id="category-name" name="name" required class="mt-1" oninput="categoryAutoSlug(this.value)" />
                </div>
                <div>
                    <flux:label for="category-slug">Slug</flux:label>
                    <flux:input id="category-slug" name="slug" required class="mt-1" />
                </div>
                <div class="flex items-center justify-end gap-3">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="modal-edit-category" class="md:w-[480px]">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Kategori</flux:heading>
            <form method="POST" id="edit-category-form" action="" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="{{ $type->value }}" />
                <div>
                    <flux:label for="edit-category-name">Nama</flux:label>
                    <flux:input id="edit-category-name" name="name" required class="mt-1" />
                </div>
                <div>
                    <flux:label for="edit-category-slug">Slug</flux:label>
                    <flux:input id="edit-category-slug" name="slug" required class="mt-1" />
                </div>
                <div class="flex items-center justify-end gap-3">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Update</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <script>
        function categoryAutoSlug(value) {
            const slugInput = document.getElementById('category-slug');

            if (slugInput) {
                slugInput.value = value.toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
            }
        }

        function openCategoryEdit(target) {
            document.getElementById('edit-category-form').action = `{{ url('content/categories') }}/${target.dataset.id}`;
            document.getElementById('edit-category-name').value = target.dataset.name || '';
            document.getElementById('edit-category-slug').value = target.dataset.slug || '';
            Flux.modal('modal-edit-category').show();
        }
    </script>
</x-layouts::app>
