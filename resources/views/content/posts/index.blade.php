<x-layouts::app :title="$section->label()">
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <flux:heading size="xl" class="font-bold">{{ $section->label() }}</flux:heading>
                <flux:subheading>Kelola konten {{ $section->label() }}: judul, kategori, status, jadwal, dan SEO.</flux:subheading>
            </div>
            <flux:button :href="route($section->adminRoute().'.create')" variant="primary" icon="plus" wire:navigate>
                Tambah {{ $section->label() }}
            </flux:button>
        </div>

        @if ($section->value === 'blog')
            <div class="flex gap-1 border-b border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('content.blog.index') }}"
                   class="border-b-2 border-blue-500 px-4 py-2.5 text-sm font-medium text-blue-600 dark:text-blue-400">
                    Artikel
                </a>
                <a href="{{ route('content.categories.index', ['type' => 'blog']) }}"
                   class="border-b-2 border-transparent px-4 py-2.5 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                   wire:navigate>
                    Kategori
                </a>
            </div>
        @endif

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route($section->adminRoute().'.index') }}" id="post-filter-form">
            <div class="admin-filter">
                <div class="admin-filter-search">
                    <flux:input name="search" value="{{ $filters['search'] }}" placeholder="Cari judul atau slug..." icon="magnifying-glass" />
                </div>

                <select name="status" onchange="this.form.submit()" class="admin-filter-select">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ $filters['status'] === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ $filters['status'] === 'published' ? 'selected' : '' }}>Published</option>
                </select>

                <select name="category" onchange="this.form.submit()" class="admin-filter-select">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) $filters['category'] === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>

                <select name="sort_by" onchange="this.form.submit()" class="admin-filter-select">
                    <option value="created_at" {{ $filters['sort_by'] === 'created_at' ? 'selected' : '' }}>Terbaru</option>
                    <option value="title" {{ $filters['sort_by'] === 'title' ? 'selected' : '' }}>Judul</option>
                    <option value="published_at" {{ $filters['sort_by'] === 'published_at' ? 'selected' : '' }}>Tanggal Terbit</option>
                </select>

                <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] }}" id="post-sort-dir" />
                <button type="button" title="{{ $filters['sort_dir'] === 'asc' ? 'Ascending' : 'Descending' }}"
                        onclick="document.getElementById('post-sort-dir').value = '{{ $filters['sort_dir'] === 'asc' ? 'desc' : 'asc' }}'; document.getElementById('post-filter-form').submit();"
                        class="admin-filter-sort">
                    @if ($filters['sort_dir'] === 'asc')
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>
                    @endif
                </button>

                <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}" />

                <flux:button type="submit" variant="outline">Cari</flux:button>

                @if ($filters['search'] || $filters['status'] || $filters['category'])
                    <a href="{{ route($section->adminRoute().'.index') }}" class="admin-filter-reset">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-shell">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th>JUDUL</th>
                            <th>KATEGORI</th>
                            <th>STATUS</th>
                            <th>TERBIT</th>
                            <th class="text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @forelse ($posts as $post)
                            <tr class="admin-table-row">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-14 w-20 shrink-0 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                                            @if ($post->cover)
                                                <img src="{{ Storage::url($post->cover) }}" alt="{{ $post->title }}" class="h-full w-full object-cover" />
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="flex items-center gap-2 font-medium text-zinc-800 dark:text-zinc-200">
                                                {{ $post->title }}
                                                @if ($post->is_featured)
                                                    <flux:badge color="amber" size="sm">Featured</flux:badge>
                                                @endif
                                            </p>
                                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $section->path() }}/{{ $post->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $post->category?->name ?? '–' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColor = match ($post->displayStatus()) {
                                            'published' => 'green',
                                            'scheduled' => 'sky',
                                            default => 'zinc',
                                        };
                                    @endphp
                                    <flux:badge :color="$statusColor" size="sm">{{ ucfirst($post->displayStatus()) }}</flux:badge>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $post->published_at?->format('d M Y H:i') ?? '–' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($post->status === 'published')
                                            <flux:button :href="$post->publicUrl()" target="_blank" icon="arrow-top-right-on-square" size="sm" square :aria-label="__('Buka halaman publik')" />
                                        @endif
                                        <flux:button :href="route($section->adminRoute().'.edit', $post->id)" icon="pencil-square" size="sm" square :aria-label="__('Edit')" wire:navigate />
                                        <form method="POST" action="{{ route($section->adminRoute().'.destroy', $post->id) }}" onsubmit="return confirm('Hapus {{ $section->label() }} ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="danger" icon="trash" size="sm" square :aria-label="__('Delete')" :disabled="auth()->user()?->isViewOnly()" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-16 text-center text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <flux:icon name="newspaper" class="size-10 text-zinc-300 dark:text-zinc-600" />
                                        <p class="font-medium">Belum ada {{ $section->label() }}</p>
                                        <p class="text-xs">Tambahkan konten baru dari tombol di kanan atas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('show-teater.partials.pagination', ['paginator' => $posts, 'perPage' => $filters['per_page'], 'pageParam' => 'page'])
        </div>
    </div>
</x-layouts::app>
