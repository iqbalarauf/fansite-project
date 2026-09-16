<?php

namespace App\Services\Google;

use App\Contracts\GoogleSheetsClient;
use App\Exceptions\GoogleSheetsException;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\ValueRange;

class GoogleApiSheetsClient implements GoogleSheetsClient
{
    /**
     * @return array{headers: array<int, string>, rows: array<int, array<string, string|null>>}
     */
    public function read(string $spreadsheetId, string $sheetName, int $startRow = 1, string $startColumn = 'A'): array
    {
        $values = $this->rawValues($spreadsheetId, $this->cellRange($sheetName, $startRow, $startColumn));

        if ($values === []) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map(
            static fn (mixed $header): string => trim((string) $header),
            $values[0],
        );

        $rows = [];

        foreach (array_slice($values, 1) as $value) {
            if ($this->isEmptyRow($value)) {
                continue;
            }

            $row = [];

            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $cell = $value[$index] ?? null;
                $row[$header] = $cell === null || $cell === '' ? null : (string) $cell;
            }

            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function write(string $spreadsheetId, string $sheetName, array $headers, array $rows, int $startRow = 1, string $startColumn = 'A'): void
    {
        $service = $this->service();

        $startColumn = $this->normalizeColumn($startColumn);
        $startColumnIndex = $this->columnToIndex($startColumn);
        $startRow = max(1, $startRow);

        $newRowCount = count($rows) + 1;
        $newColumnCount = max(1, count($headers));

        $existing = $this->rawValues($spreadsheetId, $this->cellRange($sheetName, $startRow, $startColumn));
        $oldRowCount = count($existing);
        $oldColumnCount = 0;

        foreach ($existing as $existingRow) {
            $oldColumnCount = max($oldColumnCount, count($existingRow));
        }

        $clearRowCount = max($oldRowCount, $newRowCount);
        $clearColumnCount = max($oldColumnCount, $newColumnCount);

        $service->spreadsheets_values->clear(
            $spreadsheetId,
            $this->blockRange($sheetName, $startRow, $startColumnIndex, $clearRowCount, $clearColumnCount),
            new ClearValuesRequest,
        );

        $values = [$headers];

        foreach ($rows as $row) {
            $values[] = array_map(
                static fn (string $header): string => (string) ($row[$header] ?? ''),
                $headers,
            );
        }

        $service->spreadsheets_values->update(
            $spreadsheetId,
            $this->blockRange($sheetName, $startRow, $startColumnIndex, $newRowCount, $newColumnCount),
            new ValueRange(['values' => $values]),
            ['valueInputOption' => 'RAW'],
        );
    }

    protected function service(): Sheets
    {
        $path = (string) config('services.google.service_account_path');

        if ($path === '' || ! is_file($path)) {
            throw GoogleSheetsException::notConfigured();
        }

        $client = new Client;
        $client->setApplicationName((string) config('app.name'));
        $client->setAuthConfig($path);
        $client->addScope(Sheets::SPREADSHEETS);

        return new Sheets($client);
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected function rawValues(string $spreadsheetId, string $range): array
    {
        $values = $this->service()
            ->spreadsheets_values
            ->get($spreadsheetId, $range)
            ->getValues() ?? [];

        return array_values($values);
    }

    protected function cellRange(string $sheetName, int $row, string $column): string
    {
        return $this->quoteSheet($sheetName).'!'.$this->normalizeColumn($column).max(1, $row);
    }

    protected function blockRange(string $sheetName, int $startRow, int $startColumnIndex, int $rowCount, int $columnCount): string
    {
        $endRow = max(1, $startRow) + max(1, $rowCount) - 1;
        $endColumnIndex = max(1, $startColumnIndex) + max(1, $columnCount) - 1;

        return $this->quoteSheet($sheetName)
            .'!'.$this->indexToColumn($startColumnIndex).max(1, $startRow)
            .':'.$this->indexToColumn($endColumnIndex).$endRow;
    }

    protected function quoteSheet(string $sheetName): string
    {
        if (preg_match('/^[A-Za-z0-9_]+$/', $sheetName) === 1) {
            return $sheetName;
        }

        return "'".str_replace("'", "''", $sheetName)."'";
    }

    protected function normalizeColumn(string $column): string
    {
        $column = strtoupper(trim($column));

        return preg_match('/^[A-Z]{1,3}$/', $column) === 1 ? $column : 'A';
    }

    protected function columnToIndex(string $column): int
    {
        $index = 0;

        foreach (str_split($this->normalizeColumn($column)) as $character) {
            $index = $index * 26 + (ord($character) - 64);
        }

        return max(1, $index);
    }

    protected function indexToColumn(int $index): string
    {
        $letters = '';

        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)).$letters;
            $index = intdiv($index, 26);
        }

        return $letters === '' ? 'A' : $letters;
    }

    /**
     * @param  array<int, mixed>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
