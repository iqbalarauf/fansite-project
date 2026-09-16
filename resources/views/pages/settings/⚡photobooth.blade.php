<?php

use App\Models\Photobooth;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Photobooth')] class extends Component {
    use WithFileUploads;

    public ?int $photoboothId = null;
    public string $slug = '';
    public ?string $framePath = null;
    public mixed $frameUpload = null;
    public int $columns = 2;
    public int $rows = 3;
    /** @var array<int, array{x: float|int|string, y: float|int|string, width: float|int|string, height: float|int|string}> */
    public array $photoSlots = [];
    public bool $frameOverlay = false;
    public bool $isFullOpen = true;
    public ?string $startAt = null;
    public ?string $endAt = null;
    public bool $isActive = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $photobooth = Photobooth::current();

        if ($photobooth) {
            $this->photoboothId = $photobooth->id;
            $this->slug = $photobooth->slug;
            $this->framePath = $photobooth->frame;
            $this->columns = $photobooth->columns;
            $this->rows = $photobooth->rows;
            $this->photoSlots = $photobooth->resolvedSlots();
            $this->frameOverlay = $photobooth->frame_overlay;
            $this->isFullOpen = $photobooth->is_full_open;
            $this->startAt = $photobooth->start_at?->format('Y-m-d\TH:i');
            $this->endAt = $photobooth->end_at?->format('Y-m-d\TH:i');
            $this->isActive = $photobooth->is_active;
        }

        $this->syncSlotCount();
    }

    public function updatedColumns(): void
    {
        $this->syncSlotCount();
    }

    public function updatedRows(): void
    {
        $this->syncSlotCount();
    }

    public function resetSlots(): void
    {
        $this->photoSlots = $this->gridDefaults();
    }

    public function save(): void
    {
        $this->slug = trim($this->slug) !== '' ? trim($this->slug) : 'photobooth';

        $this->validate([
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('photobooths', 'slug')->ignore($this->photoboothId)],
            'columns' => ['required', 'integer', 'min:1', 'max:4'],
            'rows' => ['required', 'integer', 'min:1', 'max:6'],
            'photoSlots' => ['required', 'array'],
            'photoSlots.*.x' => ['required', 'numeric', 'min:0', 'max:100'],
            'photoSlots.*.y' => ['required', 'numeric', 'min:0', 'max:100'],
            'photoSlots.*.width' => ['required', 'numeric', 'min:1', 'max:100'],
            'photoSlots.*.height' => ['required', 'numeric', 'min:1', 'max:100'],
            'frameOverlay' => ['boolean'],
            'frameUpload' => [$this->photoboothId ? 'nullable' : 'required', 'image', 'max:5120'],
            'startAt' => [$this->isFullOpen ? 'nullable' : 'required', 'nullable', 'date'],
            'endAt' => [$this->isFullOpen ? 'nullable' : 'required', 'nullable', 'date', 'after_or_equal:startAt'],
            'isFullOpen' => ['boolean'],
            'isActive' => ['boolean'],
        ]);

        if ($this->frameUpload) {
            if ($this->framePath) {
                Storage::disk('public')->delete($this->framePath);
            }

            $this->framePath = $this->frameUpload->store('photobooth', 'public');
            $this->frameUpload = null;
        }

        $photobooth = $this->photoboothId ? Photobooth::query()->find($this->photoboothId) : new Photobooth;
        $photobooth ??= new Photobooth;

        $photobooth->fill([
            'slug' => $this->slug,
            'frame' => $this->framePath,
            'columns' => $this->columns,
            'rows' => $this->rows,
            'slots' => $this->normalizedSlots(),
            'frame_overlay' => $this->frameOverlay,
            'is_full_open' => $this->isFullOpen,
            'start_at' => $this->isFullOpen ? null : $this->startAt,
            'end_at' => $this->isFullOpen ? null : $this->endAt,
            'is_active' => $this->isActive,
        ]);
        $photobooth->save();

        $this->photoboothId = $photobooth->id;
        $this->photoSlots = $photobooth->resolvedSlots();

        Flux::toast(variant: 'success', text: __('Photobooth berhasil disimpan.'));
    }

    public function framePreviewUrl(): ?string
    {
        if ($this->frameUpload) {
            return $this->frameUpload->temporaryUrl();
        }

        if ($this->framePath) {
            return Storage::disk('public')->url($this->framePath);
        }

        return null;
    }

    /**
     * @return array<int, array{x: float, y: float, width: float, height: float}>
     */
    private function gridDefaults(): array
    {
        $photobooth = new Photobooth;
        $photobooth->columns = $this->columns;
        $photobooth->rows = $this->rows;

        return $photobooth->defaultSlots();
    }

    private function syncSlotCount(): void
    {
        $defaults = $this->gridDefaults();
        $total = max(1, $this->columns * $this->rows);
        $photoSlots = array_values($this->photoSlots);

        for ($index = 0; $index < $total; $index++) {
            $slot = $photoSlots[$index] ?? $defaults[$index];

            $photoSlots[$index] = [
                'x' => $slot['x'] ?? $defaults[$index]['x'],
                'y' => $slot['y'] ?? $defaults[$index]['y'],
                'width' => $slot['width'] ?? $defaults[$index]['width'],
                'height' => $slot['height'] ?? $defaults[$index]['height'],
            ];
        }

        $this->photoSlots = array_slice($photoSlots, 0, $total);
    }

    /**
     * @return array<int, array{x: float, y: float, width: float, height: float}>
     */
    private function normalizedSlots(): array
    {
        return array_map(fn (array $slot): array => [
            'x' => round((float) $slot['x'], 2),
            'y' => round((float) $slot['y'], 2),
            'width' => round((float) $slot['width'], 2),
            'height' => round((float) $slot['height'], 2),
        ], array_values($this->photoSlots));
    }
}; ?>

<section class="w-full">
    <div>
        <flux:heading level="1" size="xl">{{ __('Photobooth') }}</flux:heading>
        <flux:subheading>{{ __('Atur photobooth online: slug, jadwal, layout, frame, dan posisi foto.') }}</flux:subheading>
    </div>

    <div class="w-full max-w-7xl">
        <form wire:submit="save" class="mt-5 space-y-6">
            <div class="grid items-stretch gap-6 lg:grid-cols-3">
                <div class="space-y-6">
                    <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:input wire:model="slug" :label="__('Slug URL')" type="text" placeholder="photobooth" />
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Kosongkan untuk memakai default: /photobooth. URL: /photobooth/') }}<span>{{ $slug ?: 'photobooth' }}</span></flux:text>

                        <div class="grid grid-cols-2 gap-4">
                            <flux:input wire:model.live="columns" :label="__('Kolom')" type="number" min="1" max="4" required />
                            <flux:input wire:model.live="rows" :label="__('Baris')" type="number" min="1" max="6" required />
                        </div>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Jumlah pose:') }} {{ max(1, $columns * $rows) }}</flux:text>

                        <label class="inline-flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            <input type="checkbox" wire:model="isActive" class="h-4 w-4 rounded border-zinc-300 text-blue-600">
                            {{ __('Aktifkan photobooth') }}
                        </label>

                        <label class="inline-flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            <input type="checkbox" wire:model.live="isFullOpen" class="h-4 w-4 rounded border-zinc-300 text-blue-600">
                            {{ __('Buka halaman tanpa jadwal') }}
                        </label>

                        @unless ($isFullOpen)
                            <div class="grid gap-4 md:grid-cols-2">
                                <flux:input wire:model="startAt" :label="__('Mulai (From)')" type="datetime-local" />
                                <flux:input wire:model="endAt" :label="__('Berakhir (To)')" type="datetime-local" />
                            </div>
                        @endunless

                        <label class="inline-flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            <input type="checkbox" wire:model.live="frameOverlay" class="h-4 w-4 rounded border-zinc-300 text-blue-600">
                            {{ __('Frame transparan (foto di belakang frame)') }}
                        </label>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Aktifkan bila frame berupa PNG transparan: foto digambar dulu, lalu frame ditimpa di atasnya.') }}</flux:text>
                    </div>

                    <div class="space-y-2">
                        <flux:label>{{ __('Frame (PNG, wajib)') }}</flux:label>
                        <input type="file" wire:model="frameUpload" accept="image/png,image/*" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                        @error('frameUpload') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div>
                        <flux:heading size="sm">{{ __('Preview Posisi') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __(':count foto. Nyalakan kamera untuk melihat posisi secara langsung.', ['count' => max(1, $columns * $rows)]) }}</flux:text>
                    </div>

                    <div
                        x-data="{
                            cameraOn: false,
                            stream: null,
                            error: '',
                            init() {
                                if (!window.Livewire) {
                                    return;
                                }
                                Livewire.hook('morph.updated', ({ el }) => {
                                    if (this.cameraOn && this.stream && el && this.$root.contains(el)) {
                                        this.attach();
                                    }
                                });
                            },
                            attach() {
                                if (!this.stream) {
                                    return;
                                }
                                this.$root.querySelectorAll('video').forEach((video) => {
                                    if (video.srcObject !== this.stream) {
                                        video.srcObject = this.stream;
                                    }
                                    video.play().catch(() => {});
                                });
                            },
                            async toggle() {
                                if (!this.cameraOn) {
                                    this.stop();
                                    return;
                                }
                                this.error = '';
                                try {
                                    this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 1280 } }, audio: false });
                                    this.attach();
                                } catch (e) {
                                    this.error = 'Tidak dapat mengakses kamera.';
                                    this.cameraOn = false;
                                    this.stop();
                                }
                            },
                            stop() {
                                if (this.stream) {
                                    this.stream.getTracks().forEach((track) => track.stop());
                                    this.stream = null;
                                }
                                this.$root.querySelectorAll('video').forEach((video) => {
                                    video.srcObject = null;
                                });
                            },
                        }"
                        x-on:beforeunload.window="stop()"
                        class="space-y-3"
                    >
                        <div class="flex items-center justify-between gap-4 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Preview Kamera') }}</p>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" x-model="cameraOn" x-on:change="toggle()" class="sr-only">
                                <span class="h-5 w-9 rounded-full bg-zinc-300 transition-colors dark:bg-zinc-600" :style="cameraOn ? 'background-color:#4f46e5' : ''"></span>
                                <span class="pointer-events-none absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform" :style="cameraOn ? 'transform:translateX(1rem)' : 'transform:translateX(0)'"></span>
                            </label>
                        </div>

                        <p x-show="error" x-text="error" x-cloak class="text-xs text-red-500"></p>

                        <div class="mx-auto w-full" style="max-width: calc(30rem * {{ max(1, $columns) }} / {{ max(1, $rows) }})">
                            <div class="relative w-full overflow-hidden rounded-lg border border-zinc-300 bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-900" style="aspect-ratio: {{ max(1, $columns) }} / {{ max(1, $rows) }}">
                                @if ($this->framePreviewUrl())
                                    <img src="{{ $this->framePreviewUrl() }}" alt="Frame preview" class="absolute inset-0 h-full w-full object-fill {{ $frameOverlay ? 'z-20' : 'z-10' }}" :class="cameraOn ? 'opacity-60' : ''">
                                @endif

                                <div class="absolute inset-0 {{ $frameOverlay ? 'z-10' : 'z-30' }}">
                                    @foreach ($photoSlots as $index => $slot)
                                        <div wire:key="preview-slot-{{ $index }}" class="absolute overflow-hidden" style="left: {{ $slot['x'] }}%; top: {{ $slot['y'] }}%; width: {{ $slot['width'] }}%; height: {{ $slot['height'] }}%">
                                            <video autoplay playsinline muted class="h-full w-full object-cover transition-opacity" :class="cameraOn ? 'opacity-100' : 'opacity-0'"></video>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="pointer-events-none absolute inset-0 z-40">
                                    @foreach ($photoSlots as $index => $slot)
                                        <div wire:key="outline-slot-{{ $index }}" class="absolute border-2 border-indigo-500/80 bg-indigo-500/5" style="left: {{ $slot['x'] }}%; top: {{ $slot['y'] }}%; width: {{ $slot['width'] }}%; height: {{ $slot['height'] }}%">
                                            <span class="absolute left-1 top-1 rounded bg-indigo-600 px-1 text-[10px] font-bold text-white">{{ $index + 1 }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                @unless ($this->framePreviewUrl())
                                    <div class="pointer-events-none absolute inset-x-0 bottom-2 z-50 text-center text-[10px] text-zinc-400">
                                        {{ __('Unggah frame untuk melihat preview.') }}
                                    </div>
                                @endunless
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <flux:heading size="sm">{{ __('Posisi Foto') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Atur x, y, lebar, dan tinggi tiap foto (%).') }}</flux:text>
                        </div>
                        <flux:button type="button" size="sm" variant="subtle" wire:click="resetSlots">{{ __('Reset') }}</flux:button>
                    </div>

                    <div class="max-h-[36rem] space-y-3 overflow-y-auto pr-1">
                        @foreach ($photoSlots as $index => $slot)
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Foto') }} {{ $index + 1 }}</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <flux:input wire:model.live.debounce.400ms="photoSlots.{{ $index }}.x" :label="__('X (%)')" type="number" step="0.1" min="0" max="100" />
                                    <flux:input wire:model.live.debounce.400ms="photoSlots.{{ $index }}.y" :label="__('Y (%)')" type="number" step="0.1" min="0" max="100" />
                                    <flux:input wire:model.live.debounce.400ms="photoSlots.{{ $index }}.width" :label="__('Lebar (%)')" type="number" step="0.1" min="1" max="100" />
                                    <flux:input wire:model.live.debounce.400ms="photoSlots.{{ $index }}.height" :label="__('Tinggi (%)')" type="number" step="0.1" min="1" max="100" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @error('photoSlots') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </div>
</section>
