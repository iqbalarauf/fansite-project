<?php

use App\Models\CustomPage;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Custom Pages')] class extends Component {
    public ?int $pageId = null;
    public string $title = '';
    public string $slug = '';
    public string $status = 'draft';
    public string $displayMode = 'full';
    public string $backgroundColor = 'slate';
    public string $titleAlignment = 'left';
    public array $blocks = [];
    public array $pages = [];
    public array $setlistOptions = [];
    public array $unitSongOptions = [];
    public int $selectedBlockIndex = 0;
    public ?int $selectedColumnIndex = null;
    public ?int $selectedNestedBlockIndex = null;

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
        }
    }

    public function selectNestedBlock(int $containerIndex, int $columnIndex, int $blockIndex): void
    {
        if (isset($this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks'][$blockIndex])) {
            $this->selectedBlockIndex = $containerIndex;
            $this->selectedColumnIndex = $columnIndex;
            $this->selectedNestedBlockIndex = $blockIndex;
        }
    }

    public function removeBlock(int $index): void
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        array_splice($this->blocks, $index, 1);
        $this->selectedBlockIndex = max(0, min($this->selectedBlockIndex, count($this->blocks) - 1));
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

        array_splice($this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks'], $blockIndex, 1);
        $this->selectedBlockIndex = $containerIndex;
        $this->selectedColumnIndex = null;
        $this->selectedNestedBlockIndex = null;
    }

    public function save(string $nextStatus = 'draft'): void
    {
        abort_if(auth()->user()?->isViewOnly(), 403);

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
            CustomPage::query()->findOrFail($this->pageId)->delete();
        }

        $this->newPage();
        $this->refreshPages();
        $this->redirectRoute('pages.index');
        Flux::toast(variant: 'success', text: __('Page deleted.'));
    }

    private function refreshPages(): void
    {
        $this->pages = CustomPage::query()->latest('updated_at')->get()->toArray();
    }

    private function loadStatisticOptions(): void
    {
        $this->setlistOptions = DB::table('show_teater')
            ->whereNotNull('setlist')
            ->where('setlist', '!=', '')
            ->distinct()
            ->orderBy('setlist')
            ->pluck('setlist')
            ->all();
        $this->unitSongOptions = DB::table('show_teater')
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
            'backgroundColor' => ['required', 'in:white,slate,indigo'],
            'titleAlignment' => ['required', 'in:left,center,right'],
            'blocks' => ['array', 'min:1'],
            'blocks.*.id' => ['required', 'string', 'max:80'],
            'blocks.*.type' => ['required', 'in:container,text,statistic,image,video,button,embed'],
            'blocks.*.data' => ['array'],
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
            $this->validateBlock($block, "blocks.{$index}");

            if (($block['type'] ?? null) !== 'container') {
                continue;
            }

            foreach (($block['data']['columns'] ?? []) as $columnIndex => $column) {
                foreach (($column['blocks'] ?? []) as $nestedBlockIndex => $nestedBlock) {
                    $this->validateBlock($nestedBlock, "blocks.{$index}.data.columns.{$columnIndex}.blocks.{$nestedBlockIndex}");
                }
            }
        }

        if ($nextStatus === 'published' && $this->getErrorBag()->isNotEmpty()) {
            $this->addError('status', __('Fix the block errors before publishing.'));
        }
    }

    private function validateBlock(array $block, string $path): void
    {
        $data = $block['data'] ?? [];
        $requiredField = match ($block['type']) {
                'text' => 'text',
                'statistic' => 'metric',
                'image', 'video' => 'url',
                'button' => 'label',
                'embed' => 'html',
                'container' => null,
            };

        if ($requiredField && blank($data[$requiredField] ?? null)) {
            $this->addError("{$path}.data.{$requiredField}", __('This block field is required.'));
        }

        if (in_array($block['type'], ['image', 'video', 'button'], true) && ! filter_var($data['url'] ?? null, FILTER_VALIDATE_URL)) {
            $this->addError("{$path}.data.url", __('Enter a valid URL.'));
        }

        if ($block['type'] === 'video' && ! preg_match('~(?:youtu\.be/|youtube\.com/)~i', $data['url'] ?? '')) {
            $this->addError("{$path}.data.url", __('Enter a valid YouTube URL.'));
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
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <flux:input wire:model.live="title" :label="__('Page title')" placeholder="Contoh: Profil Oshimen" />
                    <flux:input wire:model.live="slug" :label="__('Custom slug (optional)')" placeholder="profil-oshimen" />
                    <flux:select wire:model.live="titleAlignment" :label="__('Title alignment')">
                        <flux:select.option value="left">{{ __('Left') }}</flux:select.option>
                        <flux:select.option value="center">{{ __('Center') }}</flux:select.option>
                        <flux:select.option value="right">{{ __('Right') }}</flux:select.option>
                    </flux:select>
                </div>
                <flux:error name="title" />
                <flux:error name="slug" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model.live="displayMode" :label="__('Page display')">
                        <flux:select.option value="full">{{ __('Full page') }}</flux:select.option>
                        <flux:select.option value="welcome">{{ __('Welcome header and footer') }}</flux:select.option>
                    </flux:select>
                    <flux:select wire:model.live="backgroundColor" :label="__('Page background')">
                        <flux:select.option value="white">{{ __('White') }}</flux:select.option>
                        <flux:select.option value="slate">{{ __('Soft gray') }}</flux:select.option>
                        <flux:select.option value="indigo">{{ __('Indigo') }}</flux:select.option>
                    </flux:select>
                </div>

                <div wire:sort="sortBlock" class="space-y-3 rounded-2xl border border-dashed border-zinc-300 bg-zinc-100/70 p-4 dark:border-zinc-600 dark:bg-zinc-950/40">
                    @forelse ($blocks as $index => $block)
                        <article wire:sort:item="{{ $block['id'] }}" wire:key="block-{{ $block['id'] }}" wire:click="selectBlock({{ $index }})" class="group cursor-pointer rounded-2xl border bg-white p-5 shadow-sm transition dark:bg-zinc-900 {{ $selectedBlockIndex === $index ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-zinc-200 dark:border-zinc-700' }}">
                            <div class="mb-3 flex items-center justify-between gap-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                <span class="flex items-center gap-2"><flux:icon name="bars-3" class="size-4 cursor-grab" /> {{ $block['type'] }}</span>
                                <flux:button wire:click.stop="removeBlock({{ $index }})" icon="trash" size="sm" square :aria-label="__('Remove block')" />
                            </div>
                            @include('custom-pages.block-preview', ['block' => $block])
                        </article>
                    @empty
                        <div class="flex min-h-64 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-sm text-zinc-500">{{ __('Tambahkan blok dari panel kanan.') }}</div>
                    @endforelse
                </div>
            </section>

            <aside class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div>
                    <flux:heading size="sm">{{ __('Add element') }}</flux:heading>
                    <div class="mt-3 grid gap-2">
                        @foreach ([['container', 'squares-2x2', 'Container'], ['text', 'bars-3-bottom-left', 'Text'], ['statistic', 'chart-bar', 'Statistic'], ['image', 'photo', 'Image'], ['video', 'video-camera', 'YouTube video'], ['button', 'cursor-arrow-rays', 'Button'], ['embed', 'code-bracket', 'Embed HTML']] as [$type, $icon, $label])
                            <flux:button wire:click="addBlock('{{ $type }}')" variant="outline" icon="{{ $icon }}" class="justify-start">{{ __($label) }}</flux:button>
                        @endforeach
                    </div>
                </div>

                @if (isset($blocks[$selectedBlockIndex]))
                    <div class="space-y-4 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                        <flux:heading size="sm">{{ __('Edit element') }}</flux:heading>
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
                                <flux:input wire:model.live="{{ $nestedPath }}.data.url" :label="__('Image URL')" type="url" />
                                <flux:input wire:model.live="{{ $nestedPath }}.data.alt" :label="__('Alt text')" />
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
                            <flux:select wire:model.live="blocks.{{ $selectedBlockIndex }}.data.background" :label="__('Background')">
                                <flux:select.option value="white">{{ __('White') }}</flux:select.option>
                                <flux:select.option value="soft">{{ __('Soft gray') }}</flux:select.option>
                                <flux:select.option value="accent">{{ __('Accent') }}</flux:select.option>
                            </flux:select>
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

                @if ($pageId)
                    <flux:button wire:click="deletePage" wire:confirm="{{ __('Delete this page?') }}" variant="danger" icon="trash" :disabled="auth()->user()?->isViewOnly()">{{ __('Delete page') }}</flux:button>
                @endif
            </aside>
        </div>
</section>
