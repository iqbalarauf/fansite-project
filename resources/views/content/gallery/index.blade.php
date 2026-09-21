<x-layouts::app :title="__('Galeri')">
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">Galeri</flux:heading>
                <flux:subheading>Kelola galeri foto dan video fansite</flux:subheading>
            </div>
            @if ($tab === 'photos')
                <flux:modal.trigger name="modal-create-photo">
                    <flux:button variant="primary" icon="plus">Tambah Foto</flux:button>
                </flux:modal.trigger>
            @else
                <flux:modal.trigger name="modal-create-video">
                    <flux:button variant="primary" icon="plus">Tambah Video</flux:button>
                </flux:modal.trigger>
            @endif
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

        <div class="flex gap-1 border-b border-zinc-200 dark:border-zinc-700">
            <a href="{{ route('content.gallery.index', ['tab' => 'photos']) }}"
               class="border-b-2 px-4 py-2.5 text-sm font-medium transition-colors {{ $tab === 'photos' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
                Foto
                <span class="ml-1.5 rounded-full bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $photos->total() }}</span>
            </a>
            <a href="{{ route('content.gallery.index', ['tab' => 'videos']) }}"
               class="border-b-2 px-4 py-2.5 text-sm font-medium transition-colors {{ $tab === 'videos' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
                Video
                <span class="ml-1.5 rounded-full bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $videos->total() }}</span>
            </a>
        </div>

        @if ($tab === 'photos')
            <div class="admin-table-shell">
                <form method="GET" action="{{ route('content.gallery.index') }}">
                    <input type="hidden" name="tab" value="photos" />
                    <x-admin.table-toolbar :filters="$filters" search-placeholder="Cari deskripsi atau fotografer..." />
                </form>

                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead class="admin-table-head">
                            <tr>
                                <th>FOTO</th>
                                <th>DESKRIPSI</th>
                                <th>CREDIT PHOTOGRAPHER</th>
                                <th class="text-right">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="admin-table-body">
                            @forelse ($photos as $photo)
                                <tr class="admin-table-row">
                                    <td class="px-4 py-3">
                                        <div class="h-16 w-16 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                                            <img src="{{ Storage::url($photo->photo) }}" alt="{{ $photo->description }}" class="h-full w-full object-cover" />
                                        </div>
                                    </td>
                                    <td class="max-w-md px-4 py-3 text-zinc-600 dark:text-zinc-300"><p class="line-clamp-2">{{ $photo->description ?: '–' }}</p></td>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $photo->credit_photographer ?: '–' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button type="button" class="admin-action-link"
                                                    data-id="{{ $photo->id }}"
                                                    data-description="{{ $photo->description }}"
                                                    data-credit="{{ $photo->credit_photographer }}"
                                                    data-sort-order="{{ $photo->sort_order }}"
                                                    onclick="openEditPhotoModal(this)">Edit</button>
                                            <form method="POST" action="{{ route('content.gallery.photos.destroy', $photo) }}" onsubmit="return confirm('Hapus foto ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-action-danger" :disabled="auth()->user()?->isViewOnly()">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">Belum ada foto galeri.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('show-teater.partials.pagination', ['paginator' => $photos, 'perPage' => $filters['per_page'], 'pageParam' => 'photo_page'])
            </div>
        @else
            <div class="admin-table-shell">
                <form method="GET" action="{{ route('content.gallery.index') }}">
                    <input type="hidden" name="tab" value="videos" />
                    <x-admin.table-toolbar :filters="$filters" search-placeholder="Cari judul atau credit account..." />
                </form>

                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead class="admin-table-head">
                            <tr>
                                <th>PLATFORM</th>
                                <th>JUDUL</th>
                                <th>URL</th>
                                <th>CREDIT ACCOUNT</th>
                                <th class="text-right">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="admin-table-body">
                            @forelse ($videos as $video)
                                <tr class="admin-table-row">
                                    <td class="px-4 py-3">
                                        <flux:badge color="zinc" size="sm">{{ \App\Support\GalleryVideoEmbed::label($video->platform) }}</flux:badge>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-200">{{ $video->title ?: '–' }}</td>
                                    <td class="max-w-xs px-4 py-3 text-zinc-500 dark:text-zinc-400"><a href="{{ $video->url }}" target="_blank" rel="noopener" class="truncate hover:text-blue-600">{{ $video->url }}</a></td>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $video->credit_account ?: '–' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button type="button" class="admin-action-link"
                                                    data-id="{{ $video->id }}"
                                                    data-url="{{ $video->url }}"
                                                    data-title="{{ $video->title }}"
                                                    data-credit="{{ $video->credit_account }}"
                                                    data-sort-order="{{ $video->sort_order }}"
                                                    onclick="openEditVideoModal(this)">Edit</button>
                                            <form method="POST" action="{{ route('content.gallery.videos.destroy', $video) }}" onsubmit="return confirm('Hapus video ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-action-danger" :disabled="auth()->user()?->isViewOnly()">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">Belum ada video galeri.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('show-teater.partials.pagination', ['paginator' => $videos, 'perPage' => $filters['per_page'], 'pageParam' => 'video_page'])
            </div>
        @endif
    </div>

    {{-- Create Photo --}}
    <flux:modal name="modal-create-photo" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Tambah Foto</flux:heading>
            <form method="POST" action="{{ route('content.gallery.photos.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <flux:label for="create-photo-file">Photo File</flux:label>
                    <input id="create-photo-file" type="file" name="photo" accept="image/*" required class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                </div>
                <div>
                    <flux:label for="create-photo-description">Description</flux:label>
                    <flux:textarea id="create-photo-description" name="description" rows="3" class="mt-1" />
                </div>
                <div>
                    <flux:label for="create-photo-credit">Credit Photographer</flux:label>
                    <flux:input id="create-photo-credit" name="credit_photographer" class="mt-1" />
                </div>
                <div>
                    <flux:label for="create-photo-sort-order">Urutan Tampil (opsional)</flux:label>
                    <flux:input id="create-photo-sort-order" name="sort_order" type="number" min="0" placeholder="0" class="mt-1" />
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Edit Photo --}}
    <flux:modal name="modal-edit-photo" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Foto</flux:heading>
            <form method="POST" id="edit-photo-form" action="" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <flux:label for="edit-photo-file">Photo File (biarkan kosong bila tidak diganti)</flux:label>
                    <input id="edit-photo-file" type="file" name="photo" accept="image/*" class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                </div>
                <div>
                    <flux:label for="edit-photo-description">Description</flux:label>
                    <flux:textarea id="edit-photo-description" name="description" rows="3" class="mt-1" />
                </div>
                <div>
                    <flux:label for="edit-photo-credit">Credit Photographer</flux:label>
                    <flux:input id="edit-photo-credit" name="credit_photographer" class="mt-1" />
                </div>
                <div>
                    <flux:label for="edit-photo-sort-order">Urutan Tampil</flux:label>
                    <flux:input id="edit-photo-sort-order" name="sort_order" type="number" min="0" class="mt-1" />
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Update</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Create Video --}}
    <flux:modal name="modal-create-video" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Tambah Video</flux:heading>
            <form method="POST" action="{{ route('content.gallery.videos.store') }}" class="space-y-5">
                @csrf
                <div>
                    <flux:label for="create-video-url">URL Video (YouTube / Twitter-X / TikTok)</flux:label>
                    <flux:input id="create-video-url" name="url" type="url" required placeholder="https://..." class="mt-1" />
                </div>
                <div>
                    <flux:label for="create-video-title">Title</flux:label>
                    <flux:input id="create-video-title" name="title" class="mt-1" />
                </div>
                <div>
                    <flux:label for="create-video-credit">Credit Account</flux:label>
                    <flux:input id="create-video-credit" name="credit_account" class="mt-1" />
                </div>
                <div>
                    <flux:label for="create-video-sort-order">Urutan Tampil (opsional)</flux:label>
                    <flux:input id="create-video-sort-order" name="sort_order" type="number" min="0" placeholder="0" class="mt-1" />
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Edit Video --}}
    <flux:modal name="modal-edit-video" class="md:w-[560px]" variant="flyout">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Video</flux:heading>
            <form method="POST" id="edit-video-form" action="" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <flux:label for="edit-video-url">URL Video</flux:label>
                    <flux:input id="edit-video-url" name="url" type="url" required class="mt-1" />
                </div>
                <div>
                    <flux:label for="edit-video-title">Title</flux:label>
                    <flux:input id="edit-video-title" name="title" class="mt-1" />
                </div>
                <div>
                    <flux:label for="edit-video-credit">Credit Account</flux:label>
                    <flux:input id="edit-video-credit" name="credit_account" class="mt-1" />
                </div>
                <div>
                    <flux:label for="edit-video-sort-order">Urutan Tampil</flux:label>
                    <flux:input id="edit-video-sort-order" name="sort_order" type="number" min="0" class="mt-1" />
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">Update</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <script>
        function openEditPhotoModal(target) {
            document.getElementById('edit-photo-form').action = `{{ url('content/gallery/photos') }}/${target.dataset.id}`;
            document.getElementById('edit-photo-description').value = target.dataset.description || '';
            document.getElementById('edit-photo-credit').value = target.dataset.credit || '';
            document.getElementById('edit-photo-sort-order').value = target.dataset.sortOrder || '';
            Flux.modal('modal-edit-photo').show();
        }

        function openEditVideoModal(target) {
            document.getElementById('edit-video-form').action = `{{ url('content/gallery/videos') }}/${target.dataset.id}`;
            document.getElementById('edit-video-url').value = target.dataset.url || '';
            document.getElementById('edit-video-title').value = target.dataset.title || '';
            document.getElementById('edit-video-credit').value = target.dataset.credit || '';
            document.getElementById('edit-video-sort-order').value = target.dataset.sortOrder || '';
            Flux.modal('modal-edit-video').show();
        }
    </script>
</x-layouts::app>
