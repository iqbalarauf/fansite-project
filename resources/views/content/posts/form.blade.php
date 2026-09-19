@php
    $isEdit = $post !== null;
    $action = $isEdit
        ? route($section->adminRoute().'.update', $post->id)
        : route($section->adminRoute().'.store');
@endphp

<x-layouts::app :title="($isEdit ? 'Edit ' : 'Tambah ').$section->label()">
    <div class="admin-page mx-auto w-full max-w-5xl">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">{{ $isEdit ? 'Edit' : 'Tambah' }} {{ $section->label() }}</flux:heading>
                <flux:subheading>Lengkapi detail konten, kategori, penjadwalan, dan SEO.</flux:subheading>
            </div>
            <flux:button :href="route($section->adminRoute().'.index')" variant="ghost" icon="arrow-left" wire:navigate>Kembali</flux:button>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="space-y-5">
                    <div>
                        <flux:label for="post-title">Judul</flux:label>
                        <flux:input id="post-title" name="title" required class="mt-1"
                                    value="{{ old('title', $post->title ?? '') }}"
                                    oninput="contentAutoSlug(this.value)" />
                    </div>

                    <div>
                        <flux:label for="post-slug">Slug URL</flux:label>
                        <flux:input id="post-slug" name="slug" required class="mt-1"
                                    value="{{ old('slug', $post->slug ?? '') }}"
                                    data-edited="{{ old('slug', $post->slug ?? '') ? 'true' : 'false' }}" />
                        <flux:text class="mt-1 text-xs">{{ $section->path() }}/<span id="content-slug-preview">{{ old('slug', $post->slug ?? 'slug') }}</span></flux:text>
                    </div>

                    <div>
                        <flux:label for="post-excerpt">Ringkasan</flux:label>
                        <flux:textarea id="post-excerpt" name="excerpt" rows="3" class="mt-1">{{ old('excerpt', $post->excerpt ?? '') }}</flux:textarea>
                    </div>

                    <div>
                        <flux:label>Konten</flux:label>
                        <div class="mt-1">
                            <x-rich-text-editor name="content" :value="old('content', $post->content ?? '')" />
                        </div>
                    </div>
                </div>

                <aside class="space-y-5">
                    <div class="space-y-4 admin-card p-4">
                        <div>
                            <flux:label for="post-status">Status</flux:label>
                            <select id="post-status" name="status" class="admin-filter-select mt-1 w-full">
                                <option value="draft" {{ old('status', $post->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $post->status ?? 'draft') === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>

                        <div>
                            <flux:label for="post-published-at">Jadwal Terbit</flux:label>
                            <flux:input id="post-published-at" name="published_at" type="datetime-local" class="mt-1"
                                        value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i')) }}" />
                            <flux:text class="mt-1 text-xs">Kosongkan untuk terbit sekarang saat status Published.</flux:text>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                            <input type="checkbox" name="is_featured" value="1" class="rounded border-zinc-300 text-blue-600"
                                   @checked(old('is_featured', $post->is_featured ?? false))>
                            <span>Jadikan Featured / Pin</span>
                        </label>

                        <div>
                            <flux:label for="post-category">Kategori</flux:label>
                            <select id="post-category" name="category_id" class="admin-filter-select mt-1 w-full">
                                <option value="">Tanpa Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string) old('category_id', $post->category_id ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4 admin-card p-4">
                        <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Cover</p>
                        @if ($post?->cover)
                            <img src="{{ Storage::url($post->cover) }}" alt="{{ $post->title }}" class="aspect-video w-full rounded-lg object-cover" />
                        @endif
                        <input type="file" name="cover" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                    </div>
                </aside>
            </div>

            <div class="admin-card p-4">
                <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">SEO Meta</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <flux:label for="post-meta-title">Meta Title</flux:label>
                        <flux:input id="post-meta-title" name="meta_title" class="mt-1" value="{{ old('meta_title', $post->meta_title ?? '') }}" />
                    </div>
                    <div>
                        <flux:label for="post-meta-description">Meta Description</flux:label>
                        <flux:textarea id="post-meta-description" name="meta_description" rows="3" class="mt-1">{{ old('meta_description', $post->meta_description ?? '') }}</flux:textarea>
                    </div>
                    <div>
                        <flux:label for="post-og-image">OG Image</flux:label>
                        @if ($post?->og_image)
                            <img src="{{ Storage::url($post->og_image) }}" alt="OG" class="mb-2 aspect-video w-40 rounded-lg object-cover" />
                        @endif
                        <input id="post-og-image" type="file" name="og_image" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button :href="route($section->adminRoute().'.index')" variant="ghost" wire:navigate>Batal</flux:button>
                <flux:button type="submit" variant="primary" :disabled="auth()->user()?->isViewOnly()">{{ $isEdit ? 'Update' : 'Simpan' }}</flux:button>
            </div>
        </form>
    </div>

    <script>
        function contentAutoSlug(value) {
            const slugInput = document.getElementById('post-slug');
            const preview = document.getElementById('content-slug-preview');

            if (!slugInput || slugInput.dataset.edited === 'true') {
                return;
            }

            const slug = value.toLowerCase().trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');

            slugInput.value = slug;
            preview.textContent = slug || 'slug';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const slugInput = document.getElementById('post-slug');
            const preview = document.getElementById('content-slug-preview');

            if (!slugInput) {
                return;
            }

            slugInput.addEventListener('input', () => {
                slugInput.dataset.edited = 'true';
                preview.textContent = slugInput.value || 'slug';
            });
        });
    </script>
</x-layouts::app>
