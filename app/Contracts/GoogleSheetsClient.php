<?php

namespace App\Contracts;

interface GoogleSheetsClient
{
    /**
     * Read every row of a sheet using the header located at the given start cell.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<string, string|null>>}
     */
    public function read(string $spreadsheetId, string $sheetName, int $startRow = 1, string $startColumn = 'A'): array;

    /**
     * Replace the sheet content starting at the given start cell.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function write(string $spreadsheetId, string $sheetName, array $headers, array $rows, int $startRow = 1, string $startColumn = 'A'): void;
}
