<?php

use App\Models\CustomPage;
use Flux\Flux;
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
    public array $blocks = [];
    public array $pages = [];
    public int $selectedBlockIndex = 0;
    public ?int $selectedColumnIndex = null;
    public ?int $selectedNestedBlockIndex = null;

    public function mount(?int $pageId = null): void
    {
        $this->refreshPages();

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
        if (! in_array($type, ['container', 'text', 'image', 'video', 'button', 'embed'], true)) {
            return;
        }

        $this->blocks[] = [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => match ($type) {
                'container' => ['background' => 'white', 'padding' => 'medium', 'columns' => [['id' => (string) Str::uuid(), 'blocks' => []]]],
                'text' => ['text' => 'Tulis isi halaman di sini.'],
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
        if (($this->blocks[$containerIndex]['type'] ?? null) !== 'container' || ! in_array($type, ['text', 'image', 'video', 'button', 'embed'], true)) {
            return;
        }

        $this->blocks[$containerIndex]['data']['columns'][$columnIndex]['blocks'][] = [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => match ($type) {
                'text' => ['text' => 'Tulis isi halaman di sini.'],
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

    public function save(string $nextStatus = 'draft'): void
    {
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
                'blocks' => array_values($this->blocks),
            ],
        );

        $this->loadPage($page);
        $this->refreshPages();
        Flux::toast(variant: 'success', text: $nextStatus === 'published' ? __('Page published.') : __('Draft saved.'));
    }

    public function deletePage(): void
    {
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

    private function loadPage(CustomPage $page): void
    {
        $this->pageId = $page->id;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->status = $page->status;
        $this->displayMode = $page->display_mode ?? 'full';
        $this->backgroundColor = $page->background_color ?? 'slate';
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
            'blocks' => ['array', 'min:1'],
            'blocks.*.id' => ['required', 'string', 'max:80'],
            'blocks.*.type' => ['required', 'in:container,text,image,video,button,embed'],
            'blocks.*.data' => ['array'],
        ]);

        if (! in_array($nextStatus, ['draft', 'published'], true)) {
            $this->addError('status', __('Invalid page status.'));
            return;
        }

        foreach ($this->blocks as $index => $block) {
            $data = $block['data'] ?? [];
            $requiredField = match ($block['type']) {
                'text' => 'text',
                'image', 'video' => 'url',
                'button' => 'label',
                'embed' => 'html',
                'container' => null,
            };

            if ($requiredField && blank($data[$requiredField] ?? null)) {
                $this->addError("blocks.{$index}.data.{$requiredField}", __('This block field is required.'));
            }

            if (in_array($block['type'], ['image', 'video', 'button'], true) && ! filter_var($data['url'] ?? null, FILTER_VALIDATE_URL)) {
                $this->addError("blocks.{$index}.data.url", __('Enter a valid URL.'));
            }

            if ($block['type'] === 'video' && ! preg_match('~(?:youtu\.be/|youtube\.com/)~i', $data['url'] ?? '')) {
                $this->addError("blocks.{$index}.data.url", __('Enter a valid YouTube URL.'));
            }
        }

        if ($nextStatus === 'published' && $this->getErrorBag()->isNotEmpty()) {
            $this->addError('status', __('Fix the block errors before publishing.'));
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
                <flux:button wire:click="save" icon="archive-box">{{ __('Save draft') }}</flux:button>
                <flux:button wire:click="save('published')" variant="primary" icon="globe-alt">{{ __('Publish') }}</flux:button>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_280px]">
            <section class="min-w-0 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model.live="title" :label="__('Page title')" placeholder="Contoh: Profil Oshimen" />
                    <flux:input wire:model.live="slug" :label="__('Custom slug (optional)')" placeholder="profil-oshimen" />
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
                        @foreach ([['container', 'squares-2x2', 'Container'], ['text', 'bars-3-bottom-left', 'Text'], ['image', 'photo', 'Image'], ['video', 'video-camera', 'YouTube video'], ['button', 'cursor-arrow-rays', 'Button'], ['embed', 'code-bracket', 'Embed HTML']] as [$type, $icon, $label])
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
                            <flux:select wire:change="setContainerColumns({{ $selectedBlockIndex }}, $event.target.value)" :label="__('Columns')">
                                <flux:select.option value="1">{{ __('1 column') }}</flux:select.option>
                                <flux:select.option value="2">{{ __('2 columns') }}</flux:select.option>
                            </flux:select>
                            @foreach ($blocks[$selectedBlockIndex]['data']['columns'] ?? [] as $columnIndex => $column)
                                <div class="space-y-2 rounded-xl border border-dashed border-zinc-300 p-3 dark:border-zinc-600">
                                    <flux:text class="font-semibold">{{ __('Column :number', ['number' => $columnIndex + 1]) }}</flux:text>
                                    @foreach ($column['blocks'] ?? [] as $nestedIndex => $nestedBlock)
                                        <flux:button wire:click="selectNestedBlock({{ $selectedBlockIndex }}, {{ $columnIndex }}, {{ $nestedIndex }})" size="sm" variant="ghost" class="w-full justify-start">{{ $nestedBlock['type'] }}</flux:button>
                                    @endforeach
                                    <div class="grid gap-2">
                                        @foreach ([['text', 'Text'], ['image', 'Image'], ['video', 'YouTube video'], ['button', 'Button'], ['embed', 'Embed HTML']] as [$type, $label])
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
                    <flux:button wire:click="deletePage" wire:confirm="{{ __('Delete this page?') }}" variant="danger" icon="trash">{{ __('Delete page') }}</flux:button>
                @endif
            </aside>
        </div>
</section>
