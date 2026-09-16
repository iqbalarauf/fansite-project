<?php

use App\Enums\MasterData;
use App\Enums\SyncMode;
use App\Models\SheetIntegration;
use App\Services\SheetIntegration\ComparisonResult;
use App\Services\SheetIntegration\DiffRow;
use App\Services\SheetIntegration\SheetSyncService;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Sheet Integration')] class extends Component {
    /**
     * @var array<string, array{spreadsheet_id: string, sheet_name: string, header_row: int, header_column: string, mode: string, auto_direction: string}>
     */
    public array $integrations = [];

    /**
     * @var array<string, array{last_synced_at: ?string, last_sync_direction: ?string}>
     */
    public array $status = [];

    public ?string $direction = null;

    /**
     * @var array<string, array<string, mixed>>
     */
    public array $comparisons = [];

    /**
     * @var array<string, array<string, string>>
     */
    public array $resolutions = [];

    public ?string $error = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $this->loadIntegrations();
        $this->loadStatuses();
    }

    public function saveSettings(): void
    {
        $this->persistIntegrations();
        $this->loadStatuses();

        Flux::toast(variant: 'success', text: __('Pengaturan integrasi sheet berhasil disimpan.'));
    }

    public function startSync(string $direction): void
    {
        abort_unless(in_array($direction, [
            SheetSyncService::DIRECTION_DATABASE_TO_SHEET,
            SheetSyncService::DIRECTION_SHEET_TO_DATABASE,
        ], true), 404);

        $this->persistIntegrations();

        $this->direction = $direction;
        $this->buildComparisons();
    }

    public function applySync(): void
    {
        if ($this->direction === null) {
            $this->error = __('Pilih arah sinkronisasi terlebih dahulu.');

            return;
        }

        $service = app(SheetSyncService::class);
        $applied = 0;
        $skipped = 0;

        foreach (array_keys($this->comparisons) as $masterValue) {
            $masterData = MasterData::from($masterValue);
            $integration = SheetIntegration::query()->where('master_data', $masterValue)->first();

            if (! $integration instanceof SheetIntegration) {
                continue;
            }

            try {
                $result = $service->apply($integration, $this->direction, $this->resolutions[$masterValue] ?? []);
            } catch (Throwable $exception) {
                $this->error = $masterData->label().': '.$exception->getMessage();

                continue;
            }

            $applied += $result['applied'];
            $skipped += $result['skipped'];
        }

        $this->loadStatuses();
        $this->buildComparisons();

        Flux::toast(
            variant: 'success',
            text: __('Sinkronisasi selesai: :applied data diterapkan, :skipped dilewati.', [
                'applied' => $applied,
                'skipped' => $skipped,
            ]),
        );
    }

    private function persistIntegrations(): void
    {
        $this->validate([
            'integrations.*.spreadsheet_id' => ['nullable', 'string', 'max:255'],
            'integrations.*.sheet_name' => ['nullable', 'string', 'max:255'],
            'integrations.*.header_row' => ['required', 'integer', 'min:1', 'max:100000'],
            'integrations.*.header_column' => ['required', 'string', 'regex:/^[A-Za-z]{1,3}$/'],
            'integrations.*.mode' => ['required', Rule::in(array_map(
                static fn (SyncMode $mode): string => $mode->value,
                SyncMode::cases(),
            ))],
            'integrations.*.auto_direction' => ['required', Rule::in([
                SheetSyncService::DIRECTION_DATABASE_TO_SHEET,
                SheetSyncService::DIRECTION_SHEET_TO_DATABASE,
            ])],
        ], [], [
            'integrations.*.spreadsheet_id' => __('Spreadsheet ID'),
            'integrations.*.sheet_name' => __('Nama Sheet'),
            'integrations.*.header_row' => __('Baris Awal Header'),
            'integrations.*.header_column' => __('Kolom Awal Header'),
            'integrations.*.mode' => __('Mode'),
        ]);

        foreach ($this->integrations as $masterValue => $config) {
            $headerColumn = strtoupper(trim((string) $config['header_column']));

            SheetIntegration::query()->updateOrCreate(
                ['master_data' => $masterValue],
                [
                    'spreadsheet_id' => filled($config['spreadsheet_id']) ? $config['spreadsheet_id'] : null,
                    'sheet_name' => filled($config['sheet_name']) ? $config['sheet_name'] : null,
                    'header_row' => (int) $config['header_row'],
                    'header_column' => $headerColumn,
                    'mode' => $config['mode'],
                    'auto_direction' => $config['auto_direction'],
                ],
            );

            $this->integrations[$masterValue]['header_row'] = (int) $config['header_row'];
            $this->integrations[$masterValue]['header_column'] = $headerColumn;
        }
    }

    private function buildComparisons(): void
    {
        $this->comparisons = [];
        $this->resolutions = [];
        $this->error = null;

        $service = app(SheetSyncService::class);
        $masters = $this->syncableMasters();

        if ($masters === []) {
            $this->error = __('Tidak ada master data dengan mode Manual dan konfigurasi lengkap untuk disinkronkan.');

            return;
        }

        foreach ($masters as $masterData) {
            $config = $this->integrations[$masterData->value];

            try {
                $result = $service->compare(
                    $masterData,
                    $config['spreadsheet_id'],
                    $config['sheet_name'],
                    (int) $config['header_row'],
                    (string) $config['header_column'],
                );
            } catch (Throwable $exception) {
                $this->error = $masterData->label().': '.$exception->getMessage();

                continue;
            }

            $this->comparisons[$masterData->value] = $this->presentResult($result);

            foreach ($result->rows as $row) {
                $this->resolutions[$masterData->value][$row->key] = $this->direction === SheetSyncService::DIRECTION_SHEET_TO_DATABASE
                    ? 'sheet'
                    : 'database';
            }
        }
    }

    /**
     * @return array<int, MasterData>
     */
    private function syncableMasters(): array
    {
        return array_values(array_filter(
            MasterData::cases(),
            function (MasterData $masterData): bool {
                $config = $this->integrations[$masterData->value] ?? null;

                return $config !== null
                    && $config['mode'] === SyncMode::Manual->value
                    && filled($config['spreadsheet_id'])
                    && filled($config['sheet_name']);
            },
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function presentResult(ComparisonResult $result): array
    {
        return [
            'label' => $result->masterData->label(),
            'counts' => $result->countByStatus(),
            'rows' => array_map(static fn (DiffRow $row): array => [
                'key' => $row->key,
                'status' => $row->status->value,
                'status_label' => $row->status->label(),
                'status_color' => $row->status->color(),
                'is_new' => $row->isNewFromSheet(),
                'differences' => array_values(array_map(
                    static fn (array $difference, string $column): array => [
                        'column' => $column,
                        'database' => $difference['database'],
                        'sheet' => $difference['sheet'],
                    ],
                    $row->differences,
                    array_keys($row->differences),
                )),
            ], $result->rows),
        ];
    }

    private function loadIntegrations(): void
    {
        $records = SheetIntegration::query()
            ->get()
            ->keyBy(static fn (SheetIntegration $integration): string => $integration->master_data->value);

        $this->integrations = [];

        foreach (MasterData::cases() as $masterData) {
            $record = $records->get($masterData->value);

            $this->integrations[$masterData->value] = [
                'spreadsheet_id' => (string) ($record?->spreadsheet_id ?? ''),
                'sheet_name' => (string) ($record?->sheet_name ?? ''),
                'header_row' => (int) ($record?->header_row ?? 1),
                'header_column' => strtoupper((string) ($record?->header_column ?? 'A')),
                'mode' => $record?->mode?->value ?? SyncMode::Disabled->value,
                'auto_direction' => (string) ($record?->auto_direction ?? SheetSyncService::DIRECTION_DATABASE_TO_SHEET),
            ];
        }
    }

    private function loadStatuses(): void
    {
        $records = SheetIntegration::query()
            ->get()
            ->keyBy(static fn (SheetIntegration $integration): string => $integration->master_data->value);

        $this->status = [];

        foreach (MasterData::cases() as $masterData) {
            $record = $records->get($masterData->value);

            $this->status[$masterData->value] = [
                'last_synced_at' => $record?->last_synced_at?->toIso8601String(),
                'last_sync_direction' => $record?->last_sync_direction,
            ];
        }
    }
}; ?>

<section class="w-full">
    <div class="w-full max-w-7xl">
        <flux:heading level="1" size="xl">{{ __('Sheet Integration') }}</flux:heading>
        <flux:subheading>{{ __('Bandingkan dan sinkronkan Master Data antara database website dan Google Sheet.') }}</flux:subheading>

        @if ($error)
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
                {{ $error }}
            </div>
        @endif

        <form wire:submit="saveSettings" class="mt-5 space-y-6">
            <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <div>
                    <flux:heading size="sm">{{ __('Konfigurasi Spreadsheet') }}</flux:heading>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Tentukan baris & kolom awal header (mis. baris 3, kolom B). Baris header harus berisi nama kolom database. Service Account perlu akses edit ke spreadsheet.') }}</flux:text>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach (\App\Enums\MasterData::cases() as $masterData)
                        <div class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ $masterData->label() }}</p>
                                @if ($status[$masterData->value]['last_synced_at'] ?? null)
                                    <flux:text class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                        {{ __('Terakhir') }}: {{ \Illuminate\Support\Carbon::parse($status[$masterData->value]['last_synced_at'])->locale('id')->isoFormat('D MMM YYYY HH:mm') }}
                                    </flux:text>
                                @endif
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <flux:input wire:model="integrations.{{ $masterData->value }}.spreadsheet_id" :label="__('Spreadsheet ID')" type="text" placeholder="1AbC..." />
                                <flux:input wire:model="integrations.{{ $masterData->value }}.sheet_name" :label="__('Nama Sheet')" type="text" placeholder="Sheet1" />
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <flux:input wire:model="integrations.{{ $masterData->value }}.header_row" :label="__('Header Mulai Baris')" type="number" min="1" max="100000" />
                                <flux:input wire:model="integrations.{{ $masterData->value }}.header_column" :label="__('Header Mulai Kolom')" type="text" placeholder="A" maxlength="3" />
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <flux:select wire:model.live="integrations.{{ $masterData->value }}.mode" :label="__('Mode')">
                                    @foreach (\App\Enums\SyncMode::cases() as $mode)
                                        <flux:select.option value="{{ $mode->value }}">{{ $mode->label() }}</flux:select.option>
                                    @endforeach
                                </flux:select>

                                @if (($integrations[$masterData->value]['mode'] ?? '') === \App\Enums\SyncMode::Auto->value)
                                    <flux:select wire:model="integrations.{{ $masterData->value }}.auto_direction" :label="__('Arah Auto-Sync')">
                                        <flux:select.option value="database_to_sheet">{{ __('Database → Sheet') }}</flux:select.option>
                                        <flux:select.option value="sheet_to_database">{{ __('Sheet → Database') }}</flux:select.option>
                                    </flux:select>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit">{{ __('Simpan Pengaturan') }}</flux:button>
            </div>
        </form>

        <div class="mt-8 space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <div>
                <flux:heading size="sm">{{ __('Manual Sync') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pilih arah sinkronisasi, lalu tentukan pemenang tiap baris yang berbeda.') }}</flux:text>
            </div>

            <div class="flex flex-wrap gap-3">
                <flux:button type="button" variant="{{ $direction === 'database_to_sheet' ? 'primary' : 'filled' }}" icon="arrow-right" wire:click="startSync('database_to_sheet')">
                    {{ __('Sync Database to Sheet') }}
                </flux:button>
                <flux:button type="button" variant="{{ $direction === 'sheet_to_database' ? 'primary' : 'filled' }}" icon="arrow-left" wire:click="startSync('sheet_to_database')">
                    {{ __('Sync Sheet to Database') }}
                </flux:button>
            </div>

            @if ($direction)
                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ $direction === 'database_to_sheet'
                        ? __('Database menjadi sumber kebenaran: baris hanya di sheet akan dihapus, konflik memakai data database.')
                        : __('Sheet menjadi sumber kebenaran: baris hanya di database akan dihapus, konflik memakai data sheet.') }}
                </flux:text>
            @endif
        </div>

        @if ($comparisons !== [])
            <div class="mt-6 space-y-6">
                @foreach ($comparisons as $masterValue => $comparison)
                    <div class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" wire:key="comparison-{{ $masterValue }}">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <flux:heading size="sm">{{ $comparison['label'] }}</flux:heading>
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __(':count baris dibandingkan.', ['count' => count($comparison['rows'])]) }}
                                </flux:text>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <flux:badge color="zinc" size="sm">{{ __('Sama') }}: {{ $comparison['counts']['same'] }}</flux:badge>
                                <flux:badge color="amber" size="sm">{{ __('Berbeda') }}: {{ $comparison['counts']['different'] }}</flux:badge>
                                <flux:badge color="blue" size="sm">{{ __('Hanya DB') }}: {{ $comparison['counts']['only_database'] }}</flux:badge>
                                <flux:badge color="green" size="sm">{{ __('Hanya Sheet') }}: {{ $comparison['counts']['only_sheet'] }}</flux:badge>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[720px] text-left text-sm">
                                <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-3 py-2">{{ __('Key') }}</th>
                                        <th class="px-3 py-2">{{ __('Status') }}</th>
                                        <th class="px-3 py-2">{{ __('Perbedaan') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Pemenang') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($comparison['rows'] as $row)
                                        <tr class="border-b border-zinc-100 align-top dark:border-zinc-800" wire:key="row-{{ $masterValue }}-{{ $row['key'] }}">
                                            <td class="px-3 py-2 font-mono text-xs text-zinc-600 dark:text-zinc-300">
                                                {{ $row['is_new'] ? __('(baru)') : $row['key'] }}
                                            </td>
                                            <td class="px-3 py-2">
                                                <flux:badge color="{{ $row['status_color'] }}" size="sm">{{ $row['status_label'] }}</flux:badge>
                                            </td>
                                            <td class="px-3 py-2">
                                                @if ($row['differences'] === [])
                                                    <span class="text-xs text-zinc-400">—</span>
                                                @else
                                                    <div class="space-y-1">
                                                        @foreach ($row['differences'] as $difference)
                                                            <div class="text-xs text-zinc-600 dark:text-zinc-300">
                                                                <span class="font-semibold">{{ $difference['column'] }}</span>:
                                                                <span class="text-blue-600 dark:text-blue-400">{{ $difference['database'] ?? '∅' }}</span>
                                                                <span class="px-1 text-zinc-400">vs</span>
                                                                <span class="text-green-600 dark:text-green-400">{{ $difference['sheet'] ?? '∅' }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex flex-col items-end gap-1 text-xs">
                                                    <label class="inline-flex cursor-pointer items-center gap-2">
                                                        <input type="radio" wire:model="resolutions.{{ $masterValue }}.{{ $row['key'] }}" value="database" class="h-3.5 w-3.5">
                                                        {{ __('Database') }}
                                                    </label>
                                                    <label class="inline-flex cursor-pointer items-center gap-2">
                                                        <input type="radio" wire:model="resolutions.{{ $masterValue }}.{{ $row['key'] }}" value="sheet" class="h-3.5 w-3.5">
                                                        {{ __('Sheet') }}
                                                    </label>
                                                    <label class="inline-flex cursor-pointer items-center gap-2">
                                                        <input type="radio" wire:model="resolutions.{{ $masterValue }}.{{ $row['key'] }}" value="skip" class="h-3.5 w-3.5">
                                                        {{ __('Lewati') }}
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                <div class="flex items-center justify-end">
                    <flux:button type="button" variant="primary" icon="check" wire:click="applySync">
                        {{ __('Terapkan Sinkronisasi') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
</section>
