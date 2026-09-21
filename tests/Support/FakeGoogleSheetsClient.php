<?php

namespace Tests\Support;

use App\Contracts\GoogleSheetsClient;

class FakeGoogleSheetsClient implements GoogleSheetsClient
{
    /**
     * @var array<string, array{headers: array<int, string>, rows: array<int, array<string, string|null>>}>
     */
    public array $sheets = [];

    /**
     * @var array<int, array{spreadsheetId: string, sheetName: string, headers: array<int, string>, rows: array<int, array<string, mixed>>, startRow: int, startColumn: string}>
     */
    public array $writes = [];

    /**
     * @var array<int, array{spreadsheetId: string, sheetName: string, startRow: int, startColumn: string}>
     */
    public array $reads = [];

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<string, string|null>>  $rows
     */
    public function seed(string $spreadsheetId, string $sheetName, array $headers, array $rows): void
    {
        $this->sheets[$this->key($spreadsheetId, $sheetName)] = [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<string, string|null>>}
     */
    public function read(string $spreadsheetId, string $sheetName, int $startRow = 1, string $startColumn = 'A'): array
    {
        $this->reads[] = [
            'spreadsheetId' => $spreadsheetId,
            'sheetName' => $sheetName,
            'startRow' => $startRow,
            'startColumn' => $startColumn,
        ];

        return $this->sheets[$this->key($spreadsheetId, $sheetName)] ?? ['headers' => [], 'rows' => []];
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function write(string $spreadsheetId, string $sheetName, array $headers, array $rows, int $startRow = 1, string $startColumn = 'A'): void
    {
        $this->writes[] = [
            'spreadsheetId' => $spreadsheetId,
            'sheetName' => $sheetName,
            'headers' => $headers,
            'rows' => $rows,
            'startRow' => $startRow,
            'startColumn' => $startColumn,
        ];

        $this->sheets[$this->key($spreadsheetId, $sheetName)] = [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    private function key(string $spreadsheetId, string $sheetName): string
    {
        return $spreadsheetId.'|'.$sheetName;
    }
}
