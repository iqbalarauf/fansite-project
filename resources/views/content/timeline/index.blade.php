<x-layouts::app :title="__('Timeline')">
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">Timeline</flux:heading>
                <flux:subheading>Kelola data timeline infographic</flux:subheading>
            </div>
            <flux:modal.trigger name="modal-create-timeline">
                <flux:button variant="primary" icon="plus">Tambah Timeline</flux:button>
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

        <div class="admin-table-shell">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th>ID</th>
                            <th>DATE</th>
                            <th>DESCRIPTION</th>
                            <th>IMAGE</th>
                            <th class="text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @forelse ($timelines as $timeline)
                            <tr class="admin-table-row">
                                <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">#{{ $timeline->id }}</td>
                                <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">{{ $timeline->date?->translatedFormat('d F Y') }}</td>
                                <td class="max-w-md px-4 py-3 text-zinc-600 dark:text-zinc-300"><p class="line-clamp-2">{{ $timeline->description ?: '–' }}</p></td>
                                <td class="px-4 py-3">
                                    @if ($timeline->image)
                                        <div class="h-14 w-20 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                                            <img src="{{ Storage::url($timeline->image) }}" alt="Timeline {{ $timeline->id }}" class="h-full w-full object-cover" />
                                        </div>
                                    @else
                                        <span class="text-zinc-400 dark:text-zinc-500">–</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" class="admin-action-link"
                                                data-id="{{ $timeline->id }}"
                                                data-date="{{ $timeline->date?->format('Y-m-d') }}"
                                                data-description="{{ $timeline->description }}"
                                                onclick="openEditTimelineModal(this)">Edit</button>
                                        <form method="POST" action="{{ route('content.timeline.destroy', $timeline) }}" onsubmit="return confirm('Hapus timeline ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-danger" :disabled="auth()->user()?->isViewOnly()">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">Belum ada data timeline.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('show-teater.partials.pagination', ['paginator' => $timelines, 'perPage' => 15, 'pageParam' => 'page'])
        </div>
    </div>

    <flux:modal name="modal-create-timeline" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Tambah Timeline</flux:heading>
            <form method="POST" action="{{ route('content.timeline.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <flux:label for="create-timeline-date">Date</flux:label>
                    <flux:input id="create-timeline-date" name="date" type="date" required class="mt-1" />
                </div>
                <div>
                    <flux:label for="create-timeline-description">Description</flux:label>
                    <flux:textarea id="create-timeline-description" name="description" rows="4" class="mt-1" />
                </div>
                <div>
                    <flux:label for="create-timeline-image">Image</flux:label>
                    <input id="create-timeline-image" type="file" name="image" accept="image/*" class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="modal-edit-timeline" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Timeline</flux:heading>
            <form method="POST" id="edit-timeline-form" action="" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <flux:label for="edit-timeline-date">Date</flux:label>
                    <flux:input id="edit-timeline-date" name="date" type="date" required class="mt-1" />
                </div>
                <div>
                    <flux:label for="edit-timeline-description">Description</flux:label>
                    <flux:textarea id="edit-timeline-description" name="description" rows="4" class="mt-1" />
                </div>
                <div>
                    <flux:label for="edit-timeline-image">Image (biarkan kosong bila tidak diganti)</flux:label>
                    <input id="edit-timeline-image" type="file" name="image" accept="image/*" class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Update</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <script>
        function openEditTimelineModal(target) {
            document.getElementById('edit-timeline-form').action = `{{ url('content/timeline') }}/${target.dataset.id}`;
            document.getElementById('edit-timeline-date').value = target.dataset.date || '';
            document.getElementById('edit-timeline-description').value = target.dataset.description || '';
            Flux.modal('modal-edit-timeline').show();
        }
    </script>
</x-layouts::app>
