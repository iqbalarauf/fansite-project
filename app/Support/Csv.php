<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Minimal, dependency-free CSV helper (opens in Excel).
 */
final class Csv
{
    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public static function download(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            // UTF-8 BOM so Excel detects the encoding.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, array_map(
                    static fn ($value): string => $value === null ? '' : (string) $value,
                    $row,
                ));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Read a CSV file into an array of rows, auto-detecting the delimiter.
     *
     * @return array<int, array<int, string>>
     */
    public static function rows(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $rows = [];
        $delimiter = null;
        $first = true;

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");

            if ($first) {
                $line = preg_replace('/^\xEF\xBB\xBF/', '', $line) ?? $line;
                $delimiter = substr_count($line, ';') > substr_count($line, ',') ? ';' : ',';
                $first = false;
            }

            if (trim($line) === '') {
                continue;
            }

            $rows[] = str_getcsv($line, $delimiter ?? ',');
        }

        fclose($handle);

        return $rows;
    }
}
