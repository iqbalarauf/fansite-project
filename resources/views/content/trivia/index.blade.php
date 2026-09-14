<x-layouts::app :title="__('Trivia')">
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">Trivia</flux:heading>
                <flux:subheading>Kelola konten trivia</flux:subheading>
            </div>
            <flux:modal.trigger name="modal-create-trivia">
                <flux:button variant="primary" icon="plus">Tambah Trivia</flux:button>
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

        <form method="GET" action="{{ route('content.trivia.index') }}">
            <div class="admin-filter">
                <div class="admin-filter-search">
                    <flux:input name="search" value="{{ $search }}" placeholder="Cari judul atau deskripsi..." icon="magnifying-glass" />
                </div>
                <flux:button type="submit" variant="outline">Cari</flux:button>
                @if ($search !== '')
                    <a href="{{ route('content.trivia.index') }}" class="admin-filter-reset">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-shell">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th>ID</th>
                            <th>IMAGE</th>
                            <th>TITLE</th>
                            <th>DESCRIPTION</th>
                            <th class="text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @forelse ($trivias as $trivia)
                            <tr class="admin-table-row">
                                <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">#{{ $trivia->id }}</td>
                                <td class="px-4 py-3">
                                    @if ($trivia->image)
                                        <div class="h-14 w-20 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                                            <img src="{{ Storage::url($trivia->image) }}" alt="{{ $trivia->title }}" class="h-full w-full object-cover" />
                                        </div>
                                    @else
                                        <span class="text-zinc-400 dark:text-zinc-500">–</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">{{ $trivia->title }}</td>
                                <td class="max-w-md px-4 py-3 text-zinc-600 dark:text-zinc-300"><p class="line-clamp-2">{{ $trivia->description ?: '–' }}</p></td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" class="admin-action-link"
                                                data-id="{{ $trivia->id }}"
                                                data-title="{{ $trivia->title }}"
                                                data-description="{{ $trivia->description }}"
                                                onclick="openEditTriviaModal(this)">Edit</button>
                                        <form method="POST" action="{{ route('content.trivia.destroy', $trivia) }}" onsubmit="return confirm('Hapus trivia ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-danger" :disabled="auth()->user()?->isViewOnly()">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">Belum ada trivia.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('show-teater.partials.pagination', ['paginator' => $trivias, 'perPage' => 15, 'pageParam' => 'page'])
        </div>
    </div>

    <flux:modal name="modal-create-trivia" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Tambah Trivia</flux:heading>
            <form method="POST" action="{{ route('content.trivia.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <flux:label for="create-trivia-title">Title</flux:label>
                    <flux:input id="create-trivia-title" name="title" required class="mt-1" />
                </div>
                <div>
                    <flux:label for="create-trivia-description">Description</flux:label>
                    <flux:textarea id="create-trivia-description" name="description" rows="4" class="mt-1" />
                </div>
                <div>
                    <flux:label for="create-trivia-image">Image (opsional)</flux:label>
                    <input id="create-trivia-image" type="file" name="image" accept="image/*" class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="modal-edit-trivia" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Trivia</flux:heading>
            <form method="POST" id="edit-trivia-form" action="" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <flux:label for="edit-trivia-title">Title</flux:label>
                    <flux:input id="edit-trivia-title" name="title" required class="mt-1" />
                </div>
                <div>
                    <flux:label for="edit-trivia-description">Description</flux:label>
                    <flux:textarea id="edit-trivia-description" name="description" rows="4" class="mt-1" />
                </div>
                <div>
                    <flux:label for="edit-trivia-image">Image (biarkan kosong bila tidak diganti)</flux:label>
                    <input id="edit-trivia-image" type="file" name="image" accept="image/*" class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Update</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <script>
        function openEditTriviaModal(target) {
            document.getElementById('edit-trivia-form').action = `{{ url('content/trivia') }}/${target.dataset.id}`;
            document.getElementById('edit-trivia-title').value = target.dataset.title || '';
            document.getElementById('edit-trivia-description').value = target.dataset.description || '';
            Flux.modal('modal-edit-trivia').show();
        }
    </script>
</x-layouts::app>
