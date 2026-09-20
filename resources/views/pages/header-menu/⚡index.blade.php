<?php

use App\Models\CustomPage;
use App\Models\MenuItem;
use App\Models\Photobooth;
use App\Support\HeaderMenu;
use App\Support\SettingBag;
use App\Support\SettingsStore;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Header Menu')] class extends Component
{
    public string $mode = 'default';

    public ?int $editingId = null;

    public string $label = '';

    public string $type = 'link';

    public string $url = '';

    public $target = null;

    public $pageId = null;

    public $parentId = null;

    public int $sortOrder = 0;

    public bool $showForm = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $this->mode = (SettingBag::app()['header_menu_mode'] ?? 'default') === 'custom' ? 'custom' : 'default';
    }

    public function saveMode(): void
    {
        SettingsStore::setApp([
            'header_menu_mode' => $this->mode === 'custom' ? 'custom' : 'default',
        ]);

        Flux::toast(variant: 'success', text: __('Mode menu berhasil disimpan.'));
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $item = MenuItem::query()->findOrFail($id);

        $this->editingId = $item->id;
        $this->label = $item->label;
        $this->type = $item->type;
        $this->url = (string) $item->url;
        $this->target = $item->target;
        $this->pageId = $item->page_id;
        $this->parentId = $item->parent_id;
        $this->sortOrder = (int) $item->sort_order;
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(MenuItem::types())],
            'url' => ['nullable', 'string', 'max:500', 'required_if:type,'.MenuItem::TYPE_LINK],
            'target' => ['nullable', 'string', Rule::in(array_keys(HeaderMenu::builtInPages())), 'required_if:type,'.MenuItem::TYPE_PAGE_LIST],
            'pageId' => ['nullable', 'integer', Rule::exists('custom_pages', 'id'), 'required_if:type,'.MenuItem::TYPE_PAGE],
            'parentId' => ['nullable', 'integer', Rule::exists('menu_items', 'id')],
            'sortOrder' => ['integer', 'min:0'],
        ]);

        $payload = [
            'label' => $validated['label'],
            'type' => $validated['type'],
            'url' => $validated['type'] === MenuItem::TYPE_LINK ? ($validated['url'] ?? null) : null,
            'target' => $validated['type'] === MenuItem::TYPE_PAGE_LIST ? ($validated['target'] ?? null) : null,
            'page_id' => $validated['type'] === MenuItem::TYPE_PAGE ? ($validated['pageId'] ?? null) : null,
            'parent_id' => $validated['parentId'] ?? null,
            'sort_order' => $validated['sortOrder'] ?? 0,
        ];

        if ($this->editingId !== null && $payload['parent_id'] !== null && $this->wouldCreateCycle((int) $payload['parent_id'], $this->editingId)) {
            $this->addError('parentId', 'Parent tidak valid (menyebabkan siklus).');

            return;
        }

        if ($this->editingId !== null && $payload['type'] === MenuItem::TYPE_GROUP) {
            $childCount = MenuItem::query()->whereKey($this->editingId)->firstOrFail()->children()->count();

            if ($childCount === 0) {
                $this->addError('type', 'Tipe Group wajib memiliki minimal satu submenu.');

                return;
            }
        }

        if ($this->editingId !== null) {
            MenuItem::query()->findOrFail($this->editingId)->update($payload);
        } else {
            MenuItem::query()->create($payload);
        }

        $this->cancel();
        Flux::toast(variant: 'success', text: __('Item menu berhasil disimpan.'));
    }

    public function delete(int $id): void
    {
        MenuItem::query()->findOrFail($id)->delete();

        Flux::toast(variant: 'success', text: __('Item menu berhasil dihapus.'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function menuRows(): array
    {
        $all = MenuItem::query()->with('page')->ordered()->get();
        $rows = [];

        $walk = function (?int $parentId, int $depth) use (&$walk, &$rows, $all): void {
            foreach ($all->where('parent_id', $parentId) as $item) {
                $rows[] = [
                    'id' => $item->id,
                    'label' => $item->label,
                    'type' => $item->type,
                    'depth' => $depth,
                    'url' => $item->resolvedUrl(),
                    'page' => $item->page?->title,
                ];

                $walk($item->id, $depth + 1);
            }
        };

        $walk(null, 0);

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    public function parentOptions(): array
    {
        $all = MenuItem::query()->ordered()->get(['id', 'label', 'parent_id']);
        $rows = [];

        $walk = function (?int $parentId, int $depth) use (&$walk, &$rows, $all): void {
            foreach ($all->where('parent_id', $parentId) as $item) {
                if ($this->editingId !== null && $item->id === $this->editingId) {
                    continue;
                }

                $rows[$item->id] = str_repeat('— ', $depth).$item->label;
                $walk($item->id, $depth + 1);
            }
        };

        $walk(null, 0);

        return $rows;
    }

    public function availablePages(): Collection
    {
        return CustomPage::query()->where('status', 'published')->orderBy('title')->get(['id', 'title']);
    }

    /**
     * @return array<string, array{label: string, url: string}>
     */
    public function builtInPages(): array
    {
        return HeaderMenu::builtInPages();
    }

    /**
     * @return array<string, string>
     */
    public function typeLabels(): array
    {
        return [
            MenuItem::TYPE_LINK => 'Custom Link',
            MenuItem::TYPE_GROUP => 'Group (induk submenu)',
            MenuItem::TYPE_PAGE => 'Page (pilih satu)',
            MenuItem::TYPE_PAGE_LIST => 'List Page (halaman bawaan)',
            MenuItem::TYPE_BLOG => 'Blog',
            MenuItem::TYPE_NEWS => 'News',
        ];
    }

    /**
     * Read-only preview of the built-in default header menu.
     *
     * @return array<int, array{label: string, children: array<int, array{label: string}>}>
     */
    public function defaultMenuPreview(): array
    {
        $about = SettingBag::about();
        $items = [
            ['label' => 'Home', 'children' => []],
            ['label' => 'About', 'children' => [
                ['label' => (string) ($about['idol_name'] ?? 'Idol')],
                ['label' => (string) ($about['fanbase_name'] ?? 'Fansite')],
            ]],
        ];

        $articles = [];

        if (SettingBag::featureEnabled('news')) {
            $articles[] = ['label' => 'News'];
        }

        if (SettingBag::featureEnabled('blog')) {
            $articles[] = ['label' => 'Blog'];
        }

        if ($articles !== []) {
            $items[] = ['label' => 'Artikel', 'children' => $articles];
        }

        if (SettingBag::featureEnabled('magazines')) {
            $items[] = ['label' => 'Majalah', 'children' => []];
        }

        $items[] = ['label' => 'Galeri', 'children' => []];

        if (SettingBag::featureEnabled('trivia')) {
            $items[] = ['label' => 'Trivia', 'children' => []];
        }

        if (SettingBag::featureEnabled('photobooth') && Photobooth::current()) {
            $items[] = ['label' => 'Photobooth', 'children' => []];
        }

        $items[] = ['label' => 'Data', 'children' => []];
        $items[] = ['label' => 'Schedule', 'children' => []];

        return $items;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->label = '';
        $this->type = MenuItem::TYPE_LINK;
        $this->url = '';
        $this->target = null;
        $this->pageId = null;
        $this->parentId = null;
        $this->sortOrder = 0;
        $this->resetErrorBag();
    }

    private function wouldCreateCycle(int $candidateParentId, int $itemId): bool
    {
        $current = $candidateParentId;
        $guard = 0;

        while ($current !== null && $guard < 100) {
            if ($current === $itemId) {
                return true;
            }

            $current = MenuItem::query()->whereKey($current)->value('parent_id');
            $guard++;
        }

        return false;
    }
}; ?>

<section class="w-full">
    <div>
        <flux:heading level="1" size="xl">{{ __('Header Menu') }}</flux:heading>
        <flux:subheading>{{ __('Kustomisasi menu navigasi header situs publik.') }}</flux:subheading>
    </div>

    <div class="mt-5 w-full">
        @php
            $rows = $this->menuRows();
            $typeLabels = $this->typeLabels();
        @endphp

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Left column: mode + add item + form --}}
            <div class="space-y-6 lg:col-span-1">
                <div x-data="{ open: true }" class="rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <button
                        type="button"
                        x-on:click="open = ! open"
                        class="flex w-full items-center justify-between gap-3 p-4 text-start"
                        x-bind:aria-expanded="open"
                    >
                        <span>
                            <span class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Mode Menu') }}</span>
                            <span class="mt-0.5 block text-xs text-zinc-500 dark:text-zinc-400">{{ $mode === 'custom' ? __('Custom Menu') : __('Default Menu') }}</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0 text-zinc-400 transition-transform" x-bind:class="open && 'rotate-180'"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" /></svg>
                    </button>

                    <div x-show="open" x-cloak class="border-t border-zinc-200 p-4 dark:border-zinc-700">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pilih menu default bawaan aplikasi atau gunakan menu custom.') }}</p>

                        <form wire:submit="saveMode" class="mt-4 space-y-4">
                            <label class="flex items-start gap-3">
                                <input type="radio" wire:model="mode" value="default" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                                <span class="text-sm text-zinc-700 dark:text-zinc-200">
                                    <span class="font-medium">{{ __('Default Menu') }}</span>
                                    <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __('Home, About, Artikel, Majalah, Data, Schedule.') }}</span>
                                </span>
                            </label>

                            <label class="flex items-start gap-3">
                                <input type="radio" wire:model="mode" value="custom" class="mt-0.5 rounded border-zinc-300 text-blue-600">
                                <span class="text-sm text-zinc-700 dark:text-zinc-200">
                                    <span class="font-medium">{{ __('Custom Menu') }}</span>
                                    <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __('Ganti total menu dengan item yang Anda susun.') }}</span>
                                </span>
                            </label>

                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary" size="sm">{{ __('Simpan Mode') }}</flux:button>
                            </div>
                        </form>
                    </div>
                </div>

                <flux:button type="button" wire:click="create" variant="primary" icon="plus" class="w-full">
                    {{ __('Tambah Item Menu') }}
                </flux:button>

                @if ($showForm)
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ $editingId ? __('Edit Item Menu') : __('Tambah Item Menu') }}</p>

                        <form wire:submit="save" class="mt-4 space-y-4">
                            <div>
                                <flux:label for="menu-label">{{ __('Label') }}</flux:label>
                                <flux:input id="menu-label" wire:model="label" class="mt-1" />
                                @error('label') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <flux:label for="menu-type">{{ __('Tipe') }}</flux:label>
                                <select id="menu-type" wire:model.live="type" class="admin-filter-select mt-1 w-full">
                                    @foreach ($typeLabels as $value => $text)
                                        <option value="{{ $value }}">{{ $text }}</option>
                                    @endforeach
                                </select>
                                @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            @if ($type === 'link')
                                <div>
                                    <flux:label for="menu-url">{{ __('URL') }}</flux:label>
                                    <flux:input id="menu-url" wire:model="url" placeholder="https://contoh.com atau /halaman" class="mt-1" />
                                    @error('url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            @elseif ($type === 'page')
                                <div>
                                    <flux:label for="menu-page">{{ __('Page') }}</flux:label>
                                    <select id="menu-page" wire:model="pageId" class="admin-filter-select mt-1 w-full">
                                        <option value="">{{ __('— Pilih Page —') }}</option>
                                        @foreach ($this->availablePages() as $page)
                                            <option value="{{ $page->id }}">{{ $page->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('pageId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    @if ($this->availablePages()->isEmpty())
                                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">{{ __('Belum ada Custom Page yang published.') }}</p>
                                    @endif
                                </div>
                            @elseif ($type === 'group')
                                <p class="rounded-lg bg-zinc-50 px-3 py-2 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">{{ __('Group hanya berfungsi sebagai induk submenu (tanpa link). Tambahkan submenu dengan memilih Group ini sebagai Parent pada item baru.') }}</p>
                            @elseif ($type === 'page_list')
                                <div>
                                    <flux:label for="menu-target">{{ __('Halaman') }}</flux:label>
                                    <select id="menu-target" wire:model="target" class="admin-filter-select mt-1 w-full">
                                        <option value="">{{ __('— Pilih Halaman —') }}</option>
                                        @foreach ($this->builtInPages() as $key => $page)
                                            <option value="{{ $key }}">{{ $page['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('target') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            <div>
                                <flux:label for="menu-parent">{{ __('Parent (Submenu dari)') }}</flux:label>
                                <select id="menu-parent" wire:model="parentId" class="admin-filter-select mt-1 w-full">
                                    <option value="">{{ __('— Top Level —') }}</option>
                                    @foreach ($this->parentOptions() as $id => $text)
                                        <option value="{{ $id }}">{{ $text }}</option>
                                    @endforeach
                                </select>
                                @error('parentId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <flux:label for="menu-sort">{{ __('Urutan') }}</flux:label>
                                <flux:input id="menu-sort" type="number" wire:model="sortOrder" min="0" class="mt-1" />
                                @error('sortOrder') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex justify-end gap-3">
                                <flux:button type="button" variant="ghost" wire:click="cancel">{{ __('Batal') }}</flux:button>
                                <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Right column: item menu preview --}}
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between gap-3 border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Item Menu') }}</p>
                        <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $mode === 'custom' ? __('Custom Menu') : __('Default Menu') }}
                        </span>
                    </div>

                    @if ($mode === 'custom')
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($rows as $row)
                                <div class="flex items-center gap-2 py-3 pr-4" style="padding-left: {{ 16 + $row['depth'] * 22 }}px">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                            {{ $row['label'] }}
                                            <span class="ml-1 text-xs font-normal text-zinc-400">{{ $typeLabels[$row['type']] ?? $row['type'] }}</span>
                                        </p>
                                        @if ($row['url'])
                                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $row['url'] }}</p>
                                        @endif
                                    </div>

                                    <flux:button type="button" wire:click="edit({{ $row['id'] }})" size="sm" variant="ghost" icon="pencil-square" :aria-label="__('Edit')" square />
                                    <flux:button type="button" wire:click="delete({{ $row['id'] }})" wire:confirm="Hapus item ini beserta submenunya?" size="sm" variant="danger" icon="trash" :aria-label="__('Delete')" square />
                                </div>
                            @empty
                                <p class="p-6 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ __('Belum ada item menu.') }}</p>
                            @endforelse
                        </div>
                    @else
                        <ul class="space-y-3 p-4">
                            @foreach ($this->defaultMenuPreview() as $item)
                                <li>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $item['label'] }}</span>
                                        @if (! empty($item['children']))
                                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">{{ count($item['children']) }} {{ __('submenu') }}</span>
                                        @endif
                                    </div>

                                    @if (! empty($item['children']))
                                        <ul class="ms-3 mt-1.5 space-y-1 border-s border-zinc-200 ps-3 dark:border-zinc-700">
                                            @foreach ($item['children'] as $child)
                                                <li class="text-sm text-zinc-500 dark:text-zinc-400">{{ $child['label'] }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <p class="border-t border-zinc-200 p-4 text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            {{ __('Ini adalah preview menu bawaan. Pilih "Custom Menu" untuk menyusun menu sendiri.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
