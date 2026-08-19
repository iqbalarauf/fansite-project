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
    public array $blocks = [];
    public array $pages = [];
    public int $selectedBlockIndex = 0;

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
        $this->selectedBlockIndex = 0;
        $this->addBlock('container');
    }

    public function editPage(int $id): void
    {
        $this->loadPage(CustomPage::query()->findOrFail($id));
    }

    public function addBlock(string $type): void
    {
        if (! in_array($type, ['container', 'text', 'image', 'video', 'button'], true)) {
            return;
        }

        $this->blocks[] = [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => match ($type) {
                'container' => ['background' => 'white', 'padding' => 'medium'],
                'text' => ['text' => 'Tulis isi halaman di sini.'],
                'image' => ['url' => '', 'alt' => ''],
                'video' => ['url' => '', 'title' => ''],
                'button' => ['label' => 'Buka tautan', 'url' => 'https://'],
            },
        ];

        $this->selectedBlockIndex = count($this->blocks) - 1;
    }

    public function selectBlock(int $index): void
    {
        if (isset($this->blocks[$index])) {
            $this->selectedBlockIndex = $index;
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
        $this->blocks = array_values($page->blocks ?? []);
        $this->selectedBlockIndex = 0;
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
            'blocks' => ['array', 'min:1'],
            'blocks.*.id' => ['required', 'string', 'max:80'],
            'blocks.*.type' => ['required', 'in:container,text,image,video,button'],
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
                'container' => null,
            };

            if ($requiredField && blank($data[$requiredField] ?? null)) {
                $this->addError("blocks.{$index}.data.{$requiredField}", __('This block field is required.'));
            }

            if (in_array($block['type'], ['image', 'video', 'button'], true) && ! filter_var($data['url'] ?? null, FILTER_VALIDATE_URL)) {
                $this->addError("blocks.{$index}.data.url", __('Enter a valid URL.'));
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
                        @foreach ([['container', 'squares-2x2', 'Container'], ['text', 'bars-3-bottom-left', 'Text'], ['image', 'photo', 'Image'], ['video', 'video-camera', 'Video'], ['button', 'cursor-arrow-rays', 'Button']] as [$type, $icon, $label])
                            <flux:button wire:click="addBlock('{{ $type }}')" variant="outline" icon="{{ $icon }}" class="justify-start">{{ __($label) }}</flux:button>
                        @endforeach
                    </div>
                </div>

                @if (isset($blocks[$selectedBlockIndex]))
                    <div class="space-y-4 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                        <flux:heading size="sm">{{ __('Edit element') }}</flux:heading>
                        @if (in_array($blocks[$selectedBlockIndex]['type'], ['text'], true))
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
                        @elseif ($blocks[$selectedBlockIndex]['type'] === 'image')
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.url" :label="__('Image URL')" type="url" placeholder="https://..." />
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.alt" :label="__('Alt text')" />
                        @elseif ($blocks[$selectedBlockIndex]['type'] === 'video')
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.url" :label="__('Video URL')" type="url" placeholder="https://..." />
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.title" :label="__('Video title')" />
                        @elseif ($blocks[$selectedBlockIndex]['type'] === 'button')
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.label" :label="__('Label')" />
                            <flux:input wire:model.live="blocks.{{ $selectedBlockIndex }}.data.url" :label="__('Link URL')" type="url" />
                        @endif
                    </div>
                @endif

                @if ($pageId)
                    <flux:button wire:click="deletePage" wire:confirm="{{ __('Delete this page?') }}" variant="danger" icon="trash">{{ __('Delete page') }}</flux:button>
                @endif
            </aside>
        </div>
</section>
