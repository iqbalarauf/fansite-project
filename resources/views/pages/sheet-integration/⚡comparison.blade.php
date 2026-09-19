<?php

use App\Enums\MasterData;
use App\Enums\SyncMode;
use App\Models\SheetIntegration;
use App\Services\SheetIntegration\ComparisonResult;
use App\Services\SheetIntegration\DiffRow;
use App\Services\SheetIntegration\SheetSyncService;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Sheet Integration')] class extends Component
{
    /**
     * @var array<string, array{spreadsheet_id: string, sheet_name: string, header_first_cell: string, enabled: bool, auto_sync: bool, auto_direction: string}>
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
     * @var array<string, array<string, array{row?: string, columns: array<string, string>}>>
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

    public function updated(string $name, mixed $value): void
    {
        if (preg_match('/^integrations\.([a-z_]+)\.auto_sync$/', $name, $matches) !== 1) {
            return;
        }

        $this->persistIntegrations();

        if (! $value) {
            return;
        }

        $this->runAutoSync($matches[1]);
    }

    private function runAutoSync(string $masterValue): void
    {
        $integration = SheetIntegration::query()->where('master_data', $masterValue)->first();

        if (! $integration instanceof SheetIntegration || ! $integration->isConfigured()) {
            return;
        }

        try {
            $result = app(SheetSyncService::class)->fillMissing($integration);
        } catch (Throwable $exception) {
            $this->error = $integration->master_data->label().': '.$exception->getMessage();

            return;
        }

        $this->direction ??= SheetSyncService::DIRECTION_DATABASE_TO_SHEET;
        $this->loadStatuses();
        $this->buildComparisons();

        Flux::toast(
            variant: 'success',
            text: __('Auto-Sync :master: :toDatabase data diisi ke Database, :toSheet data diisi ke Sheet.', [
                'master' => $integration->master_data->label(),
                'toDatabase' => $result['to_database'],
                'toSheet' => $result['to_sheet'],
            ]),
        );
    }

    private function persistIntegrations(): void
    {
        $this->validate([
            'integrations.*.spreadsheet_id' => ['nullable', 'string', 'max:255'],
            'integrations.*.sheet_name' => ['nullable', 'string', 'max:255'],
            'integrations.*.header_first_cell' => ['required', 'string', 'regex:/^[A-Za-z]{1,3}[0-9]{1,6}$/'],
            'integrations.*.enabled' => ['boolean'],
            'integrations.*.auto_sync' => ['boolean'],
        ], [], [
            'integrations.*.spreadsheet_id' => __('Spreadsheet ID'),
            'integrations.*.sheet_name' => __('Nama Sheet'),
            'integrations.*.header_first_cell' => __('Header First Cell'),
        ]);

        foreach ($this->integrations as $masterValue => $config) {
            $cell = $this->parseHeaderCell((string) $config['header_first_cell']);

            SheetIntegration::query()->updateOrCreate(
                ['master_data' => $masterValue],
                [
                    'spreadsheet_id' => filled($config['spreadsheet_id']) ? $config['spreadsheet_id'] : null,
                    'sheet_name' => filled($config['sheet_name']) ? $config['sheet_name'] : null,
                    'header_row' => $cell['row'],
                    'header_column' => $cell['column'],
                    'mode' => ($config['enabled'] ?? false) ? SyncMode::Manual->value : SyncMode::Disabled->value,
                    'auto_sync' => (bool) ($config['auto_sync'] ?? false),
                    'auto_direction' => $config['auto_direction'] ?? SheetSyncService::DIRECTION_DATABASE_TO_SHEET,
                ],
            );

            $this->integrations[$masterValue]['header_first_cell'] = $cell['column'].$cell['row'];
        }
    }

    /**
     * @return array{row: int, column: string}
     */
    private function parseHeaderCell(string $cell): array
    {
        if (preg_match('/^([A-Za-z]{1,3})([0-9]{1,6})$/', trim($cell), $matches) !== 1) {
            return ['row' => 1, 'column' => 'A'];
        }

        return ['row' => (int) $matches[2], 'column' => strtoupper($matches[1])];
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
            $cell = $this->parseHeaderCell((string) $config['header_first_cell']);

            try {
                $result = $service->compare(
                    $masterData,
                    $config['spreadsheet_id'],
                    $config['sheet_name'],
                    $cell['row'],
                    $cell['column'],
                );
            } catch (Throwable $exception) {
                $this->error = $masterData->label().': '.$exception->getMessage();

                continue;
            }

            $this->comparisons[$masterData->value] = $this->presentResult($result);

            $default = $this->direction === SheetSyncService::DIRECTION_SHEET_TO_DATABASE ? 'sheet' : 'database';

            foreach ($result->rows as $row) {
                $entry = ['columns' => []];

                foreach (array_keys($row->differences) as $column) {
                    $entry['columns'][$column] = $default;
                }

                if ($row->database === null || $row->sheet === null) {
                    $entry['row'] = $default;
                }

                $this->resolutions[$masterData->value][$row->key] = $entry;
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
                    && (($config['enabled'] ?? false) || ($config['auto_sync'] ?? false))
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
            'columns' => $result->headers,
            'counts' => $result->countByStatus(),
            'rows' => array_map(static fn (DiffRow $row): array => [
                'key' => $row->key,
                'status' => $row->status->value,
                'status_label' => $row->status->label(),
                'status_color' => $row->status->color(),
                'is_new' => $row->isNewFromSheet(),
                'database' => $row->database,
                'sheet' => $row->sheet,
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
                'header_first_cell' => strtoupper((string) ($record?->header_column ?? 'A')).(int) ($record?->header_row ?? 1),
                'enabled' => (bool) ($record?->mode?->isEnabled() ?? false),
                'auto_sync' => (bool) ($record?->auto_sync ?? false),
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
    <div class="w-full">
        <form wire:submit="saveSettings">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <flux:heading level="1" size="xl">{{ __('Sheet Integration') }}</flux:heading>
                    <flux:subheading>{{ __('Bandingkan dan sinkronkan Master Data antara database website dan Google Sheet.') }}</flux:subheading>
                </div>

                <flux:button variant="primary" type="submit">{{ __('Simpan Pengaturan') }}</flux:button>
            </div>

            @if ($error)
                <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
                    {{ $error }}
                </div>
            @endif

            <div class="mt-6 grid items-start gap-6 lg:grid-cols-3">
                {{-- Kolom kiri (w-1/3): Konfigurasi Spreadsheet --}}
                <div class="space-y-4 lg:col-span-1">
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:heading size="sm">{{ __('Konfigurasi Spreadsheet') }}</flux:heading>
                        <flux:text class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Header First Cell menentukan baris & kolom awal header (mis. A1). Baris header harus berisi nama kolom database. Service Account perlu akses edit ke spreadsheet.') }}</flux:text>
                    </div>

                    @foreach (\App\Enums\MasterData::cases() as $masterData)
                        <div class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" wire:key="config-{{ $masterData->value }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ $masterData->label() }}</p>
                                    @if ($status[$masterData->value]['last_synced_at'] ?? null)
                                        <flux:text class="mt-0.5 text-[11px] text-zinc-500 dark:text-zinc-400">
                                            {{ __('Terakhir') }}: {{ \Illuminate\Support\Carbon::parse($status[$masterData->value]['last_synced_at'])->locale('id')->isoFormat('D MMM YYYY HH:mm') }}
                                        </flux:text>
                                    @endif
                                </div>

                                <label class="inline-flex shrink-0 cursor-pointer items-center gap-2 text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    <input type="checkbox" wire:model="integrations.{{ $masterData->value }}.enabled" class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800">
                                    {{ __('Aktif') }}
                                </label>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <flux:input wire:model="integrations.{{ $masterData->value }}.spreadsheet_id" :label="__('Spreadsheet ID')" type="text" placeholder="1AbC..." />
                                <flux:input wire:model="integrations.{{ $masterData->value }}.sheet_name" :label="__('Nama Sheet')" type="text" placeholder="Sheet1" />
                            </div>

                            <flux:input wire:model="integrations.{{ $masterData->value }}.header_first_cell" :label="__('Header First Cell')" type="text" placeholder="Contoh: A1" maxlength="9" />

                            <div class="space-y-1 rounded-lg border border-dashed border-zinc-300 p-3 dark:border-zinc-600">
                                <flux:switch wire:model.live="integrations.{{ $masterData->value }}.auto_sync" :label="__('Auto-Sync')" />
                                <flux:text class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('Jika aktif: data yang kosong di Database/Sheet otomatis diisi dari sisi yang sudah terisi (dua arah).') }}</flux:text>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Kolom kanan (w-2/3): Preview Data --}}
                <div class="space-y-4 lg:col-span-2">
                    <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <div>
                            <flux:heading size="sm">{{ __('Manual Sync') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pilih arah sinkronisasi. Tentukan nilai tiap kolom yang berbeda (Database/Sheet/Lewati); untuk baris yang jumlahnya berbeda, pilih Database, Sheet, atau Lewati.') }}</flux:text>
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
                        <div class="space-y-6">
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
                                                    <th class="px-3 py-2">{{ __('Perbandingan Kolom') }}</th>
                                                    <th class="px-3 py-2 text-right">{{ __('Jumlah Baris') }}</th>
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
                                                            @if ($comparison['columns'] === [] || ($row['database'] === null && $row['sheet'] === null))
                                                                <span class="text-xs text-zinc-400">—</span>
                                                            @else
                                                                <div class="space-y-2">
                                                                    @foreach ($comparison['columns'] as $column)
                                                                        @php
                                                                            $diff = collect($row['differences'])->firstWhere('column', $column);
                                                                            $dbValue = $row['database'][$column] ?? null;
                                                                            $sheetValue = $row['sheet'][$column] ?? null;
                                                                        @endphp

                                                                        @if ($column !== '')
                                                                            <div class="rounded-lg border p-2 text-xs {{ $diff ? 'border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-950/30' : 'border-zinc-100 dark:border-zinc-800' }}">
                                                                                <p class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $column }}</p>
                                                                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                                                                    <span class="text-blue-600 dark:text-blue-400">DB: {{ filled($dbValue) ? $dbValue : '∅' }}</span>
                                                                                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                                                                                    <span class="text-green-600 dark:text-green-400">Sheet: {{ filled($sheetValue) ? $sheetValue : '∅' }}</span>
                                                                                </div>

                                                                                @if ($diff)
                                                                                    <div class="mt-1 flex flex-wrap gap-3">
                                                                                        <label class="inline-flex cursor-pointer items-center gap-1.5">
                                                                                            <input type="radio" wire:model="resolutions.{{ $masterValue }}.{{ $row['key'] }}.columns.{{ $column }}" value="database" class="h-3.5 w-3.5">
                                                                                            {{ __('Database') }}
                                                                                        </label>
                                                                                        <label class="inline-flex cursor-pointer items-center gap-1.5">
                                                                                            <input type="radio" wire:model="resolutions.{{ $masterValue }}.{{ $row['key'] }}.columns.{{ $column }}" value="sheet" class="h-3.5 w-3.5">
                                                                                            {{ __('Sheet') }}
                                                                                        </label>
                                                                                        <label class="inline-flex cursor-pointer items-center gap-1.5">
                                                                                            <input type="radio" wire:model="resolutions.{{ $masterValue }}.{{ $row['key'] }}.columns.{{ $column }}" value="skip" class="h-3.5 w-3.5">
                                                                                            {{ __('Lewati') }}
                                                                                        </label>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            @if (in_array($row['status'], ['only_database', 'only_sheet'], true))
                                                                <div class="flex flex-col items-end gap-1 text-xs">
                                                                <label class="inline-flex cursor-pointer items-center gap-2">
                                                                    <input type="radio" wire:model="resolutions.{{ $masterValue }}.{{ $row['key'] }}.row" value="database" class="h-3.5 w-3.5">
                                                                    {{ __('Database') }}
                                                                </label>
                                                                <label class="inline-flex cursor-pointer items-center gap-2">
                                                                    <input type="radio" wire:model="resolutions.{{ $masterValue }}.{{ $row['key'] }}.row" value="sheet" class="h-3.5 w-3.5">
                                                                    {{ __('Sheet') }}
                                                                </label>
                                                                <label class="inline-flex cursor-pointer items-center gap-2">
                                                                    <input type="radio" wire:model="resolutions.{{ $masterValue }}.{{ $row['key'] }}.row" value="skip" class="h-3.5 w-3.5">
                                                                    {{ __('Lewati') }}
                                                                </label>
                                                            </div>
                                                            @else
                                                                <span class="text-xs text-zinc-400">—</span>
                                                            @endif
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
            </div>
        </form>
    </div>
</section>
