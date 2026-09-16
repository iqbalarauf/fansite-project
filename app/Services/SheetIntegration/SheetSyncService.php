<?php

namespace App\Services\SheetIntegration;

use App\Contracts\GoogleSheetsClient;
use App\Enums\DiffStatus;
use App\Enums\MasterData;
use App\Exceptions\GoogleSheetsException;
use App\Models\SheetIntegration;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class SheetSyncService
{
    public const DIRECTION_DATABASE_TO_SHEET = 'database_to_sheet';

    public const DIRECTION_SHEET_TO_DATABASE = 'sheet_to_database';

    private const NEW_KEY_PREFIX = 'new:';

    public function __construct(private readonly GoogleSheetsClient $client) {}

    public function compare(
        MasterData $masterData,
        string $spreadsheetId,
        string $sheetName,
        int $headerRow = 1,
        string $headerColumn = 'A',
    ): ComparisonResult {
        if (trim($spreadsheetId) === '' || trim($sheetName) === '') {
            throw GoogleSheetsException::missingSpreadsheet();
        }

        $columns = $masterData->columns();
        $sheet = $this->client->read($spreadsheetId, $sheetName, $headerRow, $headerColumn);

        [$databaseByKey, $sheetByKey, $order] = $this->indexRows($masterData, $sheet['rows']);

        $rows = [];

        foreach ($order as $key) {
            $rows[] = $this->diffRow(
                $key,
                $databaseByKey[$key] ?? null,
                $sheetByKey[$key] ?? null,
                $columns,
            );
        }

        return new ComparisonResult($masterData, $columns, $rows);
    }

    /**
     * @param  array<string, string>  $resolutions  row key => database|sheet|skip
     * @return array{applied: int, skipped: int}
     */
    public function apply(SheetIntegration $integration, string $direction, array $resolutions = []): array
    {
        $masterData = $integration->master_data;
        $result = $this->compare(
            $masterData,
            (string) $integration->spreadsheet_id,
            (string) $integration->sheet_name,
            $integration->header_row,
            $integration->header_column,
        );

        return $direction === self::DIRECTION_DATABASE_TO_SHEET
            ? $this->pushToSheet($integration, $result, $resolutions)
            : $this->pullToDatabase($integration, $result, $masterData, $resolutions);
    }

    /**
     * @param  array<string, string>  $resolutions
     * @return array{applied: int, skipped: int}
     */
    private function pushToSheet(SheetIntegration $integration, ComparisonResult $result, array $resolutions): array
    {
        $rows = [];
        $skipped = 0;

        foreach ($result->rows as $row) {
            $resolution = $resolutions[$row->key] ?? 'database';

            if ($resolution === 'skip') {
                $skipped++;

                continue;
            }

            $value = $resolution === 'sheet' ? $row->sheet : $row->database;

            if ($value === null) {
                $skipped++;

                continue;
            }

            $rows[] = $value;
        }

        $this->client->write(
            (string) $integration->spreadsheet_id,
            (string) $integration->sheet_name,
            $result->headers,
            $rows,
            $integration->header_row,
            $integration->header_column,
        );

        $integration->markSynced(self::DIRECTION_DATABASE_TO_SHEET);

        return ['applied' => count($rows), 'skipped' => $skipped];
    }

    /**
     * @param  array<string, string>  $resolutions
     * @return array{applied: int, skipped: int}
     */
    private function pullToDatabase(
        SheetIntegration $integration,
        ComparisonResult $result,
        MasterData $masterData,
        array $resolutions,
    ): array {
        $keyColumn = $masterData->keyColumn();
        $model = $masterData->model();
        $applied = 0;
        $skipped = 0;
        $deletedKeys = [];
        $upserts = [];

        foreach ($result->rows as $row) {
            $resolution = $resolutions[$row->key] ?? 'sheet';

            if ($resolution === 'skip') {
                $skipped++;

                continue;
            }

            $value = $resolution === 'database' ? $row->database : $row->sheet;

            if ($value === null) {
                if (! $row->isNewFromSheet()) {
                    $deletedKeys[] = $row->key;
                }

                $skipped++;

                continue;
            }

            if ($row->isNewFromSheet() && $keyColumn !== 'id') {
                $skipped++;

                continue;
            }

            $upserts[] = ['values' => $value, 'is_new' => $row->isNewFromSheet()];
        }

        foreach ($deletedKeys as $key) {
            $model::query()->where($keyColumn, $this->rawKey($key))->delete();
        }

        foreach ($upserts as $upsert) {
            $attributes = $this->attributesFor($upsert['values'], $masterData);

            if ($upsert['is_new']) {
                unset($attributes[$keyColumn]);
                $model::query()->create($attributes);
            } else {
                $model::query()->updateOrCreate(
                    [$keyColumn => $this->rawKey($this->extractKey($upsert['values'], $keyColumn))],
                    $attributes,
                );
            }

            $applied++;
        }

        $integration->markSynced(self::DIRECTION_SHEET_TO_DATABASE);

        return ['applied' => $applied, 'skipped' => $skipped];
    }

    /**
     * @param  array<int, array<string, string|null>>  $sheetRows
     * @return array{0: array<string, array<string, string|null>>, 1: array<string, array<string, string|null>>, 2: array<int, string>}
     */
    private function indexRows(MasterData $masterData, array $sheetRows): array
    {
        $keyColumn = $masterData->keyColumn();
        $databaseByKey = [];
        $order = [];

        foreach ($this->databaseRows($masterData) as $row) {
            $key = $this->normalize($row[$keyColumn] ?? null);

            if ($key === null) {
                continue;
            }

            $databaseByKey[$key] = $row;
            $order[$key] = true;
        }

        $sheetByKey = [];
        $newIndex = 0;

        foreach ($sheetRows as $row) {
            $key = $this->normalize($row[$keyColumn] ?? null);

            if ($key === null) {
                $key = self::NEW_KEY_PREFIX.$newIndex++;
            }

            $sheetByKey[$key] = $row;
            $order[$key] = true;
        }

        return [$databaseByKey, $sheetByKey, array_keys($order)];
    }

    /**
     * @param  array<string, string|null>|null  $database
     * @param  array<string, string|null>|null  $sheet
     * @param  array<int, string>  $columns
     */
    private function diffRow(string $key, ?array $database, ?array $sheet, array $columns): DiffRow
    {
        if ($database !== null && $sheet === null) {
            return new DiffRow($key, DiffStatus::OnlyDatabase, $database, null);
        }

        if ($database === null && $sheet !== null) {
            return new DiffRow($key, DiffStatus::OnlySheet, null, $sheet);
        }

        $differences = [];

        foreach ($columns as $column) {
            $databaseValue = $this->normalize($database[$column] ?? null);
            $sheetValue = $this->normalize($sheet[$column] ?? null);

            if ($databaseValue !== $sheetValue) {
                $differences[$column] = ['database' => $databaseValue, 'sheet' => $sheetValue];
            }
        }

        return new DiffRow(
            $key,
            $differences === [] ? DiffStatus::Same : DiffStatus::Different,
            $database,
            $sheet,
            $differences,
        );
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function databaseRows(MasterData $masterData): array
    {
        $model = $masterData->model();
        $columns = $masterData->columns();
        $rows = [];

        foreach ($model::query()->get() as $record) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $this->stringifyDatabaseValue($record, $column);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function stringifyDatabaseValue(Model $record, string $column): ?string
    {
        $value = $record->getAttribute($column);

        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            $cast = $record->getCasts()[$column] ?? null;
            $isDateOnly = in_array($cast, ['date', 'immutable_date'], true);

            return $isDateOnly ? $value->format('Y-m-d') : $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $this->normalize((string) $value);
    }

    /**
     * @param  array<string, string|null>  $values
     * @return array<string, string|null>
     */
    private function attributesFor(array $values, MasterData $masterData): array
    {
        $attributes = [];

        foreach ($masterData->columns() as $column) {
            $attributes[$column] = $values[$column] ?? null;
        }

        return $attributes;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    private function extractKey(array $values, string $keyColumn): string
    {
        return (string) ($values[$keyColumn] ?? '');
    }

    private function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function rawKey(string $key): int|string
    {
        return preg_match('/^-?\d+$/', $key) === 1 ? (int) $key : $key;
    }
}
