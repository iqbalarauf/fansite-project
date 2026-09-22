<?php

use App\Models\CustomPage;
use App\Support\ImageOptimizer;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Custom Pages')] class extends Component {
    use WithFileUploads;

    public ?int $pageId = null;
    public string $title = '';
    public string $slug = '';
    public string $status = 'draft';
    public string $displayMode = 'full';
    public string $backgroundColor = 'slate';
    public string $titleAlignment = 'left';
    public bool $pageInfoOpen = true;
    public bool $editElementOpen = true;
    public bool $addElementOpen = true;
    public array $blocks = [];
    public array $pages = [];
    public array $setlistOptions = [];
    public array $unitSongOptions = [];
    public int $selectedBlockIndex = 0;
    public ?int $selectedColumnIndex = null;
    public ?int $selectedNestedBlockIndex = null;
    public mixed $imageUpload = null;

    public function mount(?int $pageId = null): void
    {
        $this->refreshPages();
        $this->loadStatisticOptions();

        if ($pageId) {
            $this->loadPage(CustomPage::query()->findOrFail($pageId));

            return;
        }

        $this->addBlock('container');
    }

    public function newPage(): void
    {
        $this->reset(['pageId', 'title', 'slug', 'blocks']);
        $this->status = 'draft';
        $this->displayMode = 'full';
        $this->backgroundColor = 'slate';
        $this->titleAlignment = 'left';
        $this->selectedBlockIndex = 0;
        $this->selectedColumnIndex = null;
        $this->selectedNestedBlockIndex = null;
        $this->imageUpload = null;
        $this->addBlock('container');
    }

    public function editPage(int $id): void
    {
        $this->loadPage(CustomPage::query()->findOrFail($id));
    }

    public function addBlock(string $type): void
    {
        if (! in_array($type, ['container', 'text', 'statistic', 'image', 'video', 'button', 'embed'], true)) {
            return;
        }

        $this->blocks[] = [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => match ($type) {
                'container' => ['background' => 'white', 'padding' => 'medium', 'vertical_alignment' => 'top', 'columns' => [['id' => (string) Str::uuid(), 'blocks' => []]]],
                'text' => ['text' => 'Tulis isi halaman di sini.', 'alignment' => 'left', 'color' => '#2E2F3E', 'bold' => false, 'italic' => false, 'underline' => false],
                'statistic' => ['metric' => 'show_teater_all', 'label' => 'Total Show Teater'],
                'image' => ['url' => '', 'alt' => ''],
                'video' => ['url' => '', 'title' => ''],
                'button' => ['label' => 'Buka tautan', 'url' => 'https://'],
                'embed' => ['html' => '<div>Masukkan HTML embed di sini.</div>'],
            },
        ];

        $this->selectedBlockIndex = count($this->blocks) - 1;
        $this->selectedColumnIndex = null;
        $this->selectedNestedBlockIndex = null;
        $this->imageUpload = null;
    }

    public function addBlockToContainer(int $containerIndex, int $columnIndex, string $type): void
    {
        if (($this->blocks[$containerIndex]['type'] ?? null) !== 'container' || ! in_array($type, ['text', 'statistic', 'image', 'video', 'button', 'embed'], true)) {
            return;
        }

        $this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks'][] = [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => match ($type) {
                'text' => ['text' => 'Tulis isi halaman di sini.', 'alignment' => 'left', 'color' => '#2E2F3E', 'bold' => false, 'italic' => false, 'underline' => false],
                'statistic' => ['metric' => 'show_teater_all', 'label' => 'Total Show Teater'],
                'image' => ['url' => '', 'alt' => ''],
                'video' => ['url' => '', 'title' => ''],
                'button' => ['label' => 'Buka tautan', 'url' => 'https://'],
                'embed' => ['html' => '<div>Masukkan HTML embed di sini.</div>'],
            },
        ];

        $this->selectedBlockIndex = $containerIndex;
        $this->selectedColumnIndex = $columnIndex;
        $this->selectedNestedBlockIndex = count($this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks']) - 1;
        $this->imageUpload = null;
    }

    public function setContainerColumns(int $containerIndex, int $columnCount): void
    {
        if (($this->blocks[$containerIndex]['type'] ?? null) !== 'container' || ! in_array($columnCount, [1, 2], true)) {
            return;
        }

        $columns = $this->blocks[$containerIndex]['data']['columns'] ?? [];
        $columns = array_values(array_slice($columns, 0, $columnCount));

        while (count($columns) < $columnCount) {
            $columns[] = ['id' => (string) Str::uuid(), 'blocks' => []];
        }

        $this->blocks[$containerIndex]['data']['columns'] = $columns;
    }

    public function selectBlock(int $index): void
    {
        if (isset($this->blocks[$index])) {
            $this->selectedBlockIndex = $index;
            $this->selectedColumnIndex = null;
            $this->selectedNestedBlockIndex = null;
            $this->imageUpload = null;
            $this->resetErrorBag('imageUpload');
        }
    }

    public function selectNestedBlock(int $containerIndex, int $columnIndex, int $blockIndex): void
    {
        if (isset($this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks'][$blockIndex])) {
            $this->selectedBlockIndex = $containerIndex;
            $this->selectedColumnIndex = $columnIndex;
            $this->selectedNestedBlockIndex = $blockIndex;
            $this->imageUpload = null;
            $this->resetErrorBag('imageUpload');
        }
    }

    public function removeBlock(int $index): void
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        $this->deleteBlockFile($this->blocks[$index]);

        array_splice($this->blocks, $index, 1);
        $this->selectedBlockIndex = max(0, min($this->selectedBlockIndex, count($this->blocks) - 1));
        $this->imageUpload = null;
    }

    public function sortBlock(string $item, int $position): void
    {
        $from = collect($this->blocks)->search(fn (array $block): bool => $block['id'] === $item);

        if ($from === false || $from === $position) {
            return;
        }

        $block = $this->blocks[$from];
        array_splice($this->blocks, $from, 1);
        array_splice($this->blocks, $position, 0, [$block]);
        $this->selectedBlockIndex = $position;
    }

    public function sortNestedBlock(int $containerIndex, int $columnIndex, string $item, int $position): void
    {
        $nestedBlocks = $this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks'] ?? null;

        if (! is_array($nestedBlocks)) {
            return;
        }

        $from = collect($nestedBlocks)->search(fn (array $block): bool => $block['id'] === $item);

        if ($from === false || $from === $position) {
            return;
        }

        $block = $nestedBlocks[$from];
        array_splice($nestedBlocks, $from, 1);
        array_splice($nestedBlocks, $position, 0, [$block]);
        $this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks'] = $nestedBlocks;
        $this->selectedBlockIndex = $containerIndex;
        $this->selectedColumnIndex = $columnIndex;
        $this->selectedNestedBlockIndex = $position;
    }

    public function removeNestedBlock(int $containerIndex, int $columnIndex, int $blockIndex): void
    {
        if (! isset($this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks'][$blockIndex])) {
            return;
        }

        $this->deleteBlockFile($this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks'][$blockIndex]);

        array_splice($this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks'], $blockIndex, 1);
        $this->selectedBlockIndex = $containerIndex;
        $this->selectedColumnIndex = null;
        $this->selectedNestedBlockIndex = null;
        $this->imageUpload = null;
    }

    public function setPageBackground(string $color): void
    {
        $this->backgroundColor = $color;
    }

    public function applyPageBackground(string $color): void
    {
        $color = $this->normalizeHexColor($color);

        if ($color !== null) {
            $this->backgroundColor = $color;
        }
    }

    public function setBlockBackground(string $color): void
    {
        if (isset($this->blocks[$this->selectedBlockIndex])) {
            $this->blocks[$this->selectedBlockIndex]['data']['background'] = $color;
        }
    }

    public function applyBlockBackground(string $color): void
    {
        if (! isset($this->blocks[$this->selectedBlockIndex])) {
            return;
        }

        $color = $this->normalizeHexColor($color);

        if ($color !== null) {
            $this->blocks[$this->selectedBlockIndex]['data']['background'] = $color;
        }
    }

    public function toggleAside(string $section): void
    {
        match ($section) {
            'pageInfo' => $this->pageInfoOpen = ! $this->pageInfoOpen,
            'editElement' => $this->editElementOpen = ! $this->editElementOpen,
            'addElement' => $this->addElementOpen = ! $this->addElementOpen,
            default => null,
        };
    }

    private function normalizeHexColor(string $color): ?string
    {
        if (preg_match('/^(?:#)?([0-9A-Fa-f]{6})$/', $color, $hex)) {
            return '#'.strtolower($hex[1]);
        }

        return null;
    }

    public function save(string $nextStatus = 'draft'): void
    {
        abort_if(auth()->user()?->isViewOnly(), 403);

        if ($this->pageId) {
            Gate::authorize('update', CustomPage::query()->findOrFail($this->pageId));
        } else {
            Gate::authorize('create', CustomPage::class);
        }

        $this->resetErrorBag('imageUpload');
        $this->validatePage($nextStatus);

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $page = CustomPage::query()->updateOrCreate(
            ['id' => $this->pageId],
            [
                'title' => $this->title,
                'slug' => $this->uniqueSlug(),
                'status' => $nextStatus,
                'display_mode' => $this->displayMode,
                'background_color' => $this->backgroundColor,
                'title_alignment' => $this->titleAlignment,
                'blocks' => array_values($this->blocks),
            ],
        );

        $this->loadPage($page);
        $this->refreshPages();
        Flux::toast(variant: 'success', text: $nextStatus === 'published' ? __('Page published.') : __('Draft saved.'));
    }

    public function deletePage(): void
    {
        abort_if(auth()->user()?->isViewOnly(), 403);

        if ($this->pageId) {
            $page = CustomPage::query()->findOrFail($this->pageId);

            Gate::authorize('delete', $page);

            foreach ($page->blocks ?? [] as $block) {
                $this->deleteBlockFile($block);
            }

            $page->delete();
        }

        $this->newPage();
        $this->refreshPages();
        $this->redirectRoute('pages.index');
        Flux::toast(variant: 'success', text: __('Page deleted.'));
    }

    public function uploadImage(): void
    {
        abort_if(auth()->user()?->isViewOnly(), 403);

        $this->validate([
            'imageUpload' => ['nullable', 'image', 'max:3072'],
        ]);

        if (! $this->imageUpload) {
            return;
        }

        $data = $this->selectedImageBlockData();

        if (! is_array($data)) {
            return;
        }

        if (! blank($data['storage_path'] ?? null)) {
            Storage::disk('public')->delete($data['storage_path']);
        }

        $data['storage_path'] = ImageOptimizer::store($this->imageUpload, 'pages');
        $data['url'] = '';
        $this->updateSelectedImageData($data);
        $this->imageUpload = null;

        Flux::toast(variant: 'success', text: __('Image uploaded.'));
    }

    public function removeImage(): void
    {
        abort_if(auth()->user()?->isViewOnly(), 403);

        $data = $this->selectedImageBlockData();

        if (! is_array($data)) {
            return;
        }

        if (! blank($data['storage_path'] ?? null)) {
            Storage::disk('public')->delete($data['storage_path']);
        }

        $data['storage_path'] = null;
        $data['url'] = '';
        $this->updateSelectedImageData($data);
        $this->imageUpload = null;

        Flux::toast(variant: 'success', text: __('Image removed.'));
    }

    public function selectedImagePreviewUrl(): ?string
    {
        $data = $this->selectedImageBlockData();

        if (! is_array($data)) {
            return null;
        }

        if ($this->imageUpload && $this->imageUpload->isPreviewable()) {
            return $this->imageUpload->temporaryUrl();
        }

        if (! blank($data['storage_path'] ?? null)) {
            return Storage::disk('public')->url($data['storage_path']);
        }

        return filled($data['url'] ?? null) ? $data['url'] : null;
    }

    private function isSelectedBlockImage(): bool
    {
        if ($this->selectedColumnIndex === null || $this->selectedNestedBlockIndex === null) {
            return ($this->blocks[$this->selectedBlockIndex]['type'] ?? null) === 'image';
        }

        return ($this->blocks[$this->selectedBlockIndex]['data']['columns'][$this->selectedColumnIndex]['blocks'][$this->selectedNestedBlockIndex]['type'] ?? null) === 'image';
    }

    private function selectedImageBlockData(): ?array
    {
        if (! $this->isSelectedBlockImage()) {
            return null;
        }

        if ($this->selectedColumnIndex === null || $this->selectedNestedBlockIndex === null) {
            return $this->blocks[$this->selectedBlockIndex]['data'] ?? null;
        }

        return $this->blocks[$this->selectedBlockIndex]['data']['columns'][$this->selectedColumnIndex]['blocks'][$this->selectedNestedBlockIndex]['data'] ?? null;
    }

    private function updateSelectedImageData(array $data): void
    {
        if ($this->selectedColumnIndex === null || $this->selectedNestedBlockIndex === null) {
            $this->blocks[$this->selectedBlockIndex]['data'] = $data;

            return;
        }

        $this->blocks[$this->selectedBlockIndex]['data']['columns'][$this->selectedColumnIndex]['blocks'][$this->selectedNestedBlockIndex]['data'] = $data;
    }

    private function deleteBlockFile(array $block): void
    {
        if (($block['type'] ?? null) === 'container') {
            foreach (($block['data']['columns'] ?? []) as $column) {
                foreach (($column['blocks'] ?? []) as $nestedBlock) {
                    $this->deleteBlockFile($nestedBlock);
                }
            }

            return;
        }

        if (($block['type'] ?? null) !== 'image' || blank($block['data']['storage_path'] ?? null)) {
            return;
        }

        Storage::disk('public')->delete($block['data']['storage_path']);
    }

    private function refreshPages(): void
    {
        $this->pages = CustomPage::query()->latest('updated_at')->get()->toArray();
    }

    private function loadStatisticOptions(): void
    {
        $this->setlistOptions = DB::table('show_teater')
            ->whereNull('deleted_at')
            ->whereNotNull('setlist')
            ->where('setlist', '!=', '')
            ->distinct()
            ->orderBy('setlist')
            ->pluck('setlist')
            ->all();
        $this->unitSongOptions = DB::table('show_teater')
            ->whereNull('deleted_at')
            ->whereNotNull('unit_song')
            ->where('unit_song', '!=', '')
            ->distinct()
            ->orderBy('unit_song')
            ->pluck('unit_song')
            ->all();
    }

    private function loadPage(CustomPage $page): void
    {
        $this->pageId = $page->id;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->status = $page->status;
        $this->displayMode = $page->display_mode ?? 'full';
        $this->backgroundColor = $page->background_color ?? 'slate';
        $this->titleAlignment = $page->title_alignment ?? 'left';
        $this->blocks = array_values($page->blocks ?? []);
        $this->selectedBlockIndex = 0;
        $this->selectedColumnIndex = null;
        $this->selectedNestedBlockIndex = null;
        $this->imageUpload = null;
    }

    private function uniqueSlug(): string
    {
        $baseSlug = Str::slug($this->slug ?: $this->title);
        $slug = $baseSlug;
        $suffix = 2;

        while (CustomPage::query()->where('slug', $slug)->where('id', '!=', $this->pageId)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }

    private function validatePage(string $nextStatus): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('custom_pages', 'slug')->ignore($this->pageId)],
            'displayMode' => ['required', 'in:full,welcome'],
            'backgroundColor' => ['required', 'regex:/^(?:white|slate|indigo|#[0-9A-Fa-f]{6})$/'],
            'titleAlignment' => ['required', 'in:left,center,right'],
            'blocks' => ['array', 'min:1'],
            'blocks.*.id' => ['required', 'string', 'max:80'],
            'blocks.*.type' => ['required', 'in:container,text,statistic,image,video,button,embed'],
            'blocks.*.data' => ['array'],
            'blocks.*.data.background' => ['nullable', 'regex:/^(?:white|soft|accent|#[0-9A-Fa-f]{6})$/'],
            'blocks.*.data.vertical_alignment' => ['nullable', 'in:top,middle,bottom'],
            'blocks.*.data.alignment' => ['nullable', 'in:left,center,right,justify'],
            'blocks.*.data.color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'blocks.*.data.bold' => ['nullable', 'boolean'],
            'blocks.*.data.italic' => ['nullable', 'boolean'],
            'blocks.*.data.underline' => ['nullable', 'boolean'],
            'blocks.*.data.metric' => ['nullable', 'in:show_teater_all,show_teater_date_range,show_teater_setlist,unit_song_all,unit_song_date_range,unit_song_setlist,center_unit_song_all,center_unit_song_unit_song,center_unit_song_setlist,center_unit_song_date_range,global_center_date_range,global_center_setlist,live_streaming_time,live_streaming_row,live_streaming_platform'],
            'blocks.*.data.columns.*.blocks.*.id' => ['required', 'string', 'max:80'],
            'blocks.*.data.columns.*.blocks.*.type' => ['required', 'in:text,statistic,image,video,button,embed'],
            'blocks.*.data.columns.*.blocks.*.data' => ['array'],
            'blocks.*.data.columns.*.blocks.*.data.alignment' => ['nullable', 'in:left,center,right,justify'],
            'blocks.*.data.columns.*.blocks.*.data.color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'blocks.*.data.columns.*.blocks.*.data.bold' => ['nullable', 'boolean'],
            'blocks.*.data.columns.*.blocks.*.data.italic' => ['nullable', 'boolean'],
            'blocks.*.data.columns.*.blocks.*.data.underline' => ['nullable', 'boolean'],
            'blocks.*.data.columns.*.blocks.*.data.metric' => ['nullable', 'in:show_teater_all,show_teater_date_range,show_teater_setlist,unit_song_all,unit_song_date_range,unit_song_setlist,center_unit_song_all,center_unit_song_unit_song,center_unit_song_setlist,center_unit_song_date_range,global_center_date_range,global_center_setlist,live_streaming_time,live_streaming_row,live_streaming_platform'],
        ]);

        if (! in_array($nextStatus, ['draft', 'published'], true)) {
            $this->addError('status', __('Invalid page status.'));
            return;
        }

        foreach ($this->blocks as $index => $block) {
            $this->validateBlockRecursive($block, "blocks.{$index}");
        }

        if ($nextStatus === 'published' && $this->getErrorBag()->isNotEmpty()) {
            $this->addError('status', __('Fix the block errors before publishing.'));
        }
    }

    private function validateBlockRecursive(array $block, string $path): void
    {
        if (($block['type'] ?? null) === 'container') {
            foreach (($block['data']['columns'] ?? []) as $columnIndex => $column) {
                foreach (($column['blocks'] ?? []) as $nestedBlockIndex => $nestedBlock) {
                    $this->validateBlockRecursive(
                        $nestedBlock,
                        "{$path}.data.columns.{$columnIndex}.blocks.{$nestedBlockIndex}",
                    );
                }
            }

            return;
        }

        $this->validateBlock($block, $path);
    }

    private function validateBlock(array $block, string $path): void
    {
        $data = $block['data'] ?? [];
        $type = $block['type'] ?? null;

        if ($type === 'image') {
            $hasStoredFile = ! blank($data['storage_path'] ?? null);
            $url = $data['url'] ?? null;

            if (! $hasStoredFile && blank($url)) {
                $this->addError("{$path}.data.url", __('This block field is required.'));
            }

            if (! $hasStoredFile && ! blank($url) && ! filter_var($url, FILTER_VALIDATE_URL)) {
                $this->addError("{$path}.data.url", __('Enter a valid URL.'));
            }

            return;
        }

        $requiredField = match ($type) {
            'text' => 'text',
            'statistic' => 'metric',
            'video' => 'url',
            'button' => 'label',
            'embed' => 'html',
            default => null,
        };

        if ($requiredField && blank($data[$requiredField] ?? null)) {
            $this->addError("{$path}.data.{$requiredField}", __('This block field is required.'));
        }

        $urlFields = match ($type) {
            'video', 'button' => ['url'],
            default => [],
        };

        foreach ($urlFields as $urlField) {
            $url = $data[$urlField] ?? null;

            if ($type === 'video' && ! preg_match('~(?:youtu\.be/|youtube\.com/)~i', (string) $url)) {
                $this->addError("{$path}.data.{$urlField}", __('Enter a valid YouTube URL.'));
            } elseif (! filter_var($url, FILTER_VALIDATE_URL)) {
                $this->addError("{$path}.data.{$urlField}", __('Enter a valid URL.'));
            }
        }
    }
};
?>

<section class="mx-auto w-full max-w-7xl space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <flux:heading size="xl">{{ __('Tambah Halaman Baru') }}</flux:heading>
                <flux:subheading>{{ __('Buat halaman publik dengan blok yang dapat dipindahkan.') }}</flux:subheading>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button wire:click="save" icon="archive-box" :disabled="auth()->user()?->isViewOnly()">{{ __('Save draft') }}</flux:button>
                <flux:button wire:click="save('published')" variant="primary" icon="globe-alt" :disabled="auth()->user()?->isViewOnly()">{{ __('Publish') }}</flux:button>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_280px]">
            <section class="min-w-0 space-y-4">
                <flux:input wire:model.live="title" :label="__('Page title')" placeholder="Contoh: Profil Oshimen" />
                <flux:error name="title" />

                <div wire:sort="sortBlock" class="space-y-3 rounded-2xl border border-dashed border-zinc-300 bg-zinc-100/70 p-4 dark:border-zinc-600 dark:bg-zinc-950/40">
                    @forelse ($blocks as $index => $block)
                        <article wire:sort:item="{{ $block['id'] }}" wire:key="block-{{ $block['id'] }}" wire:click="selectBlock({{ $index }})" class="group cursor-pointer rounded-2xl border bg-white p-5 shadow-sm transition dark:bg-zinc-900 {{ $selectedBlockIndex === $index ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-zinc-200 dark:border-zinc-700' }}">
                            <div class="mb-3 flex items-center justify-between gap-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                <span class="flex items-center gap-2"><flux:icon name="bars-3" class="size-4 cursor-grab" /> {{ $block['type'] }}</span>
                                <flux:button wire:click.stop="removeBlock({{ $index }})" icon="trash" size="sm" square :aria-label="__('Remove block')" />
                            </div>
                            <x-custom-page-block :block="$block" preview />
                        </article>
                    @empty
                        <div class="flex min-h-64 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-sm text-zinc-500">{{ __('Tambahkan blok dari panel kanan.') }}</div>
                    @endforelse
                </div>
            </section>

            <aside class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div>
                    <button type="button" wire:click="toggleAside('pageInfo')" :aria-expanded="$pageInfoOpen" class="flex w-full items-center justify-between gap-2 px-1 py-0.5 text-left">
                        <flux:heading size="sm">{{ __('Page Information') }}</flux:heading>
                        <flux:icon name="chevron-down" class="{{ $pageInfoOpen ? '' : '-rotate-90' }} size-4 shrink-0 text-zinc-400 transition-transform" />
                    </button>
                    @if ($pageInfoOpen)
                        <div class="mt-4 space-y-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                            <flux:input wire:model.live="slug" :label="__('Custom slug (optional)')" placeholder="profil-oshimen" />
                            <flux:error name="slug" />
                            <flux:select wire:model.live="titleAlignment" :label="__('Title alignment')">
                                <flux:select.option value="left">{{ __('Left') }}</flux:select.option>
                                <flux:select.option value="center">{{ __('Center') }}</flux:select.option>
                                <flux:select.option value="right">{{ __('Right') }}</flux:select.option>
                            </flux:select>
                            <flux:select wire:model.live="displayMode" :label="__('Page display')">
                                <flux:select.option value="full">{{ __('Full page') }}</flux:select.option>
                                <flux:select.option value="welcome">{{ __('Welcome header and footer') }}</flux:select.option>
                            </flux:select>
                            @include('custom-pages.background-fields', [
                                'valuePath' => 'backgroundColor',
                                'label' => __('Page background'),
                                'presetHexes' => ['white' => '#FFFFFF', 'slate' => '#F1F5F9', 'indigo' => '#EEF2FF'],
                                'presetLabels' => ['white' => 'White', 'slate' => 'Soft gray', 'indigo' => 'Indigo'],
                                'onChange' => 'applyPageBackground',
                                'onPreset' => 'setPageBackground',
                            ])
                        </div>
                    @endif
                </div>

                @if (isset($blocks[$selectedBlockIndex]))
                    <div>
                        <button type="button" wire:click="toggleAside('editElement')" :aria-expanded="$editElementOpen" class="flex w-full items-center justify-between gap-2 px-1 py-0.5 text-left">
                            <flux:heading size="sm">{{ __('Edit element') }}</flux:heading>
                            <flux:icon name="chevron-down" class="{{ $editElementOpen ? '' : '-rotate-90' }} size-4 shrink-0 text-zinc-400 transition-transform" />
                        </button>
                        @if ($editElementOpen)
                            <div class="mt-4 space-y-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                        @if ($selectedColumnIndex !== null && $selectedNestedBlockIndex !== null && isset($blocks[$selectedBlockIndex]['data']['columns'][$selectedColumnIndex]['blocks'][$selectedNestedBlockIndex]))
                            @php($nestedPath = "blocks.{$selectedBlockIndex}.data.columns.{$selectedColumnIndex}.blocks.{$selectedNestedBlockIndex}")
                            @php($nestedType = $blocks[$selectedBlockIndex]['data']['columns'][$selectedColumnIndex]['blocks'][$selectedNestedBlockIndex]['type'])
                            <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Editing nested :type', ['type' => $nestedType]) }}</flux:text>
                            @if ($nestedType === 'text')
                                <flux:textarea wire:model.live="{{ $nestedPath }}.data.text" :label="__('Text')" rows="6" />
                                <flux:select wire:model.live="{{ $nestedPath }}.data.alignment" :label="__('Text alignment')">
                                    @foreach (['left' => 'Left', 'center' => 'Center', 'right' => 'Right', 'justify' => 'Justify'] as $value => $label)
                                        <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:input wire:model.live="{{ $nestedPath }}.data.color" :label="__('Text color')" type="color" />
                                <div class="flex flex-wrap gap-3">
                                    <flux:checkbox wire:model.live="{{ $nestedPath }}.data.bold" :label="__('Bold')" />
                                    <flux:checkbox wire:model.live="{{ $nestedPath }}.data.italic" :label="__('Italic')" />
                                    <flux:checkbox wire:model.live="{{ $nestedPath }}.data.underline" :label="__('Underline')" />
                                </div>
                            @elseif ($nestedType === 'statistic')
                                @include('custom-pages.statistic-fields', ['path' => $nestedPath])
                            @elseif ($nestedType === 'image')
                                <flux:input wire:model.live="{{ $nestedPath }}.data.url" :label="__('Image URL')" type="url" placeholder="https://..." />
                                <flux:input wire:model.live="{{ $nestedPath }}.data.alt" :label="__('Alt text')" />
                                <div class="space-y-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                                    <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Upload image') }}</flux:text>
                                    @if ($this->selectedImagePreviewUrl())
                                        <img src="{{ $this->selectedImagePreviewUrl() }}" alt="" class="max-h-40 w-full rounded-xl object-cover">
                                    @endif
                                    <input type="file" wire:model="imageUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                    <div class="flex flex-wrap gap-2">
                                        <flux:button wire:click="uploadImage" size="sm" variant="primary" icon="arrow-up-tray">{{ __('Upload') }}</flux:button>
                                        @if (! empty($blocks[$selectedBlockIndex]['data']['columns'][$selectedColumnIndex]['blocks'][$selectedNestedBlockIndex]['data']['storage_path'] ?? null))
                                            <flux:button wire:click="removeImage" size="sm" variant="danger" icon="trash">{{ __('Remove image') }}</flux:button>
                                        @endif
                                    </div>
                                    <flux:error name="imageUpload" />
                                </div>
                            @elseif ($nestedType === 'video')
                                <flux:input wire:model.live="{{ $nestedPath }}.data.url" :label="__('YouTube URL')" type="url" />
                                <flux:input wire:model.live="{{ $nestedPath }}.data.title" :label="__('Video title')" />
                            @elseif ($nestedType === 'button')
                                <flux:input wire:model.live="{{ $nestedPath }}.data.label" :label="__('Label')" />
                                <flux:input wire:model.live="{{ $nestedPath }}.data.url" :label="__('Link URL')" type="url" />
                            @elseif ($nestedType === 'embed')
                                <flux:textarea wire:model.live="{{ $nestedPath }}.data.html" :label="__('Embed HTML')" rows="8" />
                            @endif
                        @elseif (in_array($blocks[$selectedBlockIndex]['type'], ['text'], true))
                            <flux:textarea wire:model.live="blocks.{{ $selectedBlockIndex }}.data.text" :label="__('Text')" rows="6" />
                            <flux:select wire:model.live="blocks.{{ $selectedBlockIndex }}.data.alignment" :label="__('Text alignment')">
                                @foreach (['left' => 'Left', 'center' => 'Center', 'right' => 'Right', 'justify' => 'Justify'] as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.color" :label="__('Text color')" type="color" />
                            <div class="flex flex-wrap gap-3">
                                <flux:checkbox wire:model.live="blocks.{{ $selectedBlockIndex }}.data.bold" :label="__('Bold')" />
                                <flux:checkbox wire:model.live="blocks.{{ $selectedBlockIndex }}.data.italic" :label="__('Italic')" />
                                <flux:checkbox wire:model.live="blocks.{{ $selectedBlockIndex }}.data.underline" :label="__('Underline')" />
                            </div>
                        @elseif ($blocks[$selectedBlockIndex]['type'] === 'statistic')
                            @include('custom-pages.statistic-fields', ['path' => "blocks.{$selectedBlockIndex}"])
                        @elseif ($blocks[$selectedBlockIndex]['type'] === 'container')
                            @include('custom-pages.background-fields', [
                                'valuePath' => "blocks.{$selectedBlockIndex}.data.background",
                                'label' => __('Element background'),
                                'presetHexes' => ['white' => '#FFFFFF', 'soft' => '#F4F4F5', 'accent' => '#4F46E5'],
                                'presetLabels' => ['white' => 'White', 'soft' => 'Soft gray', 'accent' => 'Accent'],
                                'onChange' => 'applyBlockBackground',
                                'onPreset' => 'setBlockBackground',
                            ])
                            <flux:select wire:model.live="blocks.{{ $selectedBlockIndex }}.data.padding" :label="__('Padding')">
                                <flux:select.option value="small">{{ __('Small') }}</flux:select.option>
                                <flux:select.option value="medium">{{ __('Medium') }}</flux:select.option>
                                <flux:select.option value="large">{{ __('Large') }}</flux:select.option>
                            </flux:select>
                            <flux:select wire:model.live="blocks.{{ $selectedBlockIndex }}.data.vertical_alignment" :label="__('Vertical alignment')">
                                <flux:select.option value="top">{{ __('Top') }}</flux:select.option>
                                <flux:select.option value="middle">{{ __('Middle') }}</flux:select.option>
                                <flux:select.option value="bottom">{{ __('Bottom') }}</flux:select.option>
                            </flux:select>
                            <flux:select wire:change="setContainerColumns({{ $selectedBlockIndex }}, $event.target.value)" :label="__('Columns')">
                                <flux:select.option value="1">{{ __('1 column') }}</flux:select.option>
                                <flux:select.option value="2">{{ __('2 columns') }}</flux:select.option>
                            </flux:select>
                            @foreach ($blocks[$selectedBlockIndex]['data']['columns'] ?? [] as $columnIndex => $column)
                                <div class="space-y-2 rounded-xl border border-dashed border-zinc-300 p-3 dark:border-zinc-600">
                                    <flux:text class="font-semibold">{{ __('Column :number', ['number' => $columnIndex + 1]) }}</flux:text>
                                    <div wire:sort="sortNestedBlock({{ $selectedBlockIndex }}, {{ $columnIndex }})" class="space-y-1">
                                        @foreach ($column['blocks'] ?? [] as $nestedIndex => $nestedBlock)
                                            <div wire:sort:item="{{ $nestedBlock['id'] }}" wire:key="nested-block-{{ $nestedBlock['id'] }}" class="flex items-center gap-1">
                                                <flux:icon wire:sort:handle name="bars-3" class="size-4 cursor-grab text-zinc-400" />
                                                <flux:button wire:click="selectNestedBlock({{ $selectedBlockIndex }}, {{ $columnIndex }}, {{ $nestedIndex }})" size="sm" variant="ghost" class="min-w-0 flex-1 justify-start">{{ $nestedBlock['type'] }}</flux:button>
                                                <flux:button wire:click="removeNestedBlock({{ $selectedBlockIndex }}, {{ $columnIndex }}, {{ $nestedIndex }})" icon="trash" size="sm" square :aria-label="__('Remove nested block')" />
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="grid gap-2">
                                        @foreach ([['text', 'Text'], ['statistic', 'Statistic'], ['image', 'Image'], ['video', 'YouTube video'], ['button', 'Button'], ['embed', 'Embed HTML']] as [$type, $label])
                                            <flux:button wire:click="addBlockToContainer({{ $selectedBlockIndex }}, {{ $columnIndex }}, '{{ $type }}')" size="sm" variant="outline">{{ __('Add :element', ['element' => __($label)]) }}</flux:button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @elseif ($blocks[$selectedBlockIndex]['type'] === 'image')
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.url" :label="__('Image URL')" type="url" placeholder="https://..." />
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.alt" :label="__('Alt text')" />
                            <div class="space-y-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Upload image') }}</flux:text>
                                @if ($this->selectedImagePreviewUrl())
                                    <img src="{{ $this->selectedImagePreviewUrl() }}" alt="" class="max-h-40 w-full rounded-xl object-cover">
                                @endif
                                <input type="file" wire:model="imageUpload" accept="image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                                <div class="flex flex-wrap gap-2">
                                    <flux:button wire:click="uploadImage" size="sm" variant="primary" icon="arrow-up-tray">{{ __('Upload') }}</flux:button>
                                    @if (! empty($blocks[$selectedBlockIndex]['data']['storage_path'] ?? null))
                                        <flux:button wire:click="removeImage" size="sm" variant="danger" icon="trash">{{ __('Remove image') }}</flux:button>
                                    @endif
                                </div>
                                <flux:error name="imageUpload" />
                            </div>
                        @elseif ($blocks[$selectedBlockIndex]['type'] === 'video')
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.url" :label="__('YouTube URL')" type="url" placeholder="https://youtube.com/watch?v=..." />
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.title" :label="__('Video title')" />
                        @elseif ($blocks[$selectedBlockIndex]['type'] === 'button')
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.label" :label="__('Label')" />
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.url" :label="__('Link URL')" type="url" />
                        @elseif ($blocks[$selectedBlockIndex]['type'] === 'embed')
                            <flux:textarea wire:model.live="blocks.{{ $selectedBlockIndex }}.data.html" :label="__('Embed HTML')" rows="8" />
                        @endif
                            </div>
                        @endif
                    </div>
                @endif

                <div>
                    <button type="button" wire:click="toggleAside('addElement')" :aria-expanded="$addElementOpen" class="flex w-full items-center justify-between gap-2 px-1 py-0.5 text-left">
                        <flux:heading size="sm">{{ __('Add element') }}</flux:heading>
                        <flux:icon name="chevron-down" class="{{ $addElementOpen ? '' : '-rotate-90' }} size-4 shrink-0 text-zinc-400 transition-transform" />
                    </button>
                    @if ($addElementOpen)
                        <div class="mt-4 space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                            <div class="grid gap-2">
                                @foreach ([['container', 'squares-2x2', 'Container'], ['text', 'bars-3-bottom-left', 'Text'], ['statistic', 'chart-bar', 'Statistic'], ['image', 'photo', 'Image'], ['video', 'video-camera', 'YouTube video'], ['button', 'cursor-arrow-rays', 'Button'], ['embed', 'code-bracket', 'Embed HTML']] as [$type, $icon, $label])
                                    <flux:button wire:click="addBlock('{{ $type }}')" variant="outline" icon="{{ $icon }}" class="justify-start">{{ __($label) }}</flux:button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                @if ($pageId)
                    <flux:button wire:click="deletePage" wire:confirm="{{ __('Delete this page?') }}" variant="danger" icon="trash" :disabled="auth()->user()?->isViewOnly()">{{ __('Delete page') }}</flux:button>
                @endif
            </aside>
        </div>
</section>
