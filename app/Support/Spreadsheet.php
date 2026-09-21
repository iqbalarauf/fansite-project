<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Dependency-free XLSX (Office Open XML) writer/reader plus CSV fallback.
 */
final class Spreadsheet
{
    public const XLSX_MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public static function download(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'xlsx');
        self::writeXlsx($path, $headers, $rows);

        $content = (string) file_get_contents($path);
        @unlink($path);

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, $filename, ['Content-Type' => self::XLSX_MIME]);
    }

    /**
     * Read an uploaded spreadsheet (xlsx or csv) into rows.
     *
     * @return array<int, array<int, string>>
     */
    public static function rows(UploadedFile $file): array
    {
        $path = (string) $file->getRealPath();
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if ($extension === 'xlsx' || $extension === '') {
            return self::xlsxRows($path);
        }

        return Csv::rows($path);
    }

    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public static function writeXlsx(string $path, array $headers, iterable $rows): void
    {
        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
            return;
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addFromString('_rels/.rels', self::rootRelsXml());
        $zip->addFromString('xl/workbook.xml', self::workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheetXml($headers, $rows));
        $zip->close();
    }

    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    private static function sheetXml(array $headers, iterable $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="'.self::NS.'"><sheetData>';

        $rowNumber = 1;

        foreach ([$headers, ...self::normalizeRows($rows)] as $row) {
            $xml .= '<row r="'.$rowNumber.'">';

            foreach (array_values($row) as $columnIndex => $value) {
                $reference = self::columnLetter($columnIndex).$rowNumber;
                $escaped = htmlspecialchars($value === null ? '' : (string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');

                $xml .= '<c r="'.$reference.'" t="inlineStr"><is><t xml:space="preserve">'.$escaped.'</t></is></c>';
            }

            $xml .= '</row>';
            $rowNumber++;
        }

        return $xml.'</sheetData></worksheet>';
    }

    /**
     * @param  iterable<int, array<int, mixed>>  $rows
     * @return array<int, array<int, mixed>>
     */
    private static function normalizeRows(iterable $rows): array
    {
        return $rows instanceof \Traversable ? iterator_to_array($rows, false) : $rows;
    }

    /**
     * @return array<int, array<int, string>>
     */
    private static function xlsxRows(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            return [];
        }

        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return [];
        }

        $sheetPath = self::firstSheetPath($zip);

        if ($sheetPath === null) {
            $zip->close();

            return [];
        }

        $sheetXml = (string) $zip->getFromName($sheetPath);
        $sharedXml = (string) ($zip->getFromName('xl/sharedStrings.xml') ?: '');
        $zip->close();

        $document = @simplexml_load_string($sheetXml);

        if ($document === false) {
            return [];
        }

        $shared = self::sharedStrings($sharedXml);
        $rows = [];

        foreach ($document->children(self::NS)->sheetData->row as $row) {
            $cells = [];

            foreach ($row->children(self::NS)->c as $cell) {
                $attributes = $cell->attributes();
                $reference = isset($attributes['r']) ? (string) $attributes['r'] : '';
                $type = isset($attributes['t']) ? (string) $attributes['t'] : '';
                $column = $reference !== '' ? self::columnIndex($reference) : count($cells);

                $cells[$column] = self::cellValue($cell, $type, $shared);
            }

            if ($cells === []) {
                continue;
            }

            $line = [];

            for ($index = 0, $max = max(array_keys($cells)); $index <= $max; $index++) {
                $line[] = $cells[$index] ?? '';
            }

            $rows[] = $line;
        }

        return $rows;
    }

    private static function firstSheetPath(ZipArchive $zip): ?string
    {
        if ($zip->locateName('xl/worksheets/sheet1.xml') !== false) {
            return 'xl/worksheets/sheet1.xml';
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);

            if (preg_match('#^xl/worksheets/sheet[0-9]+\.xml$#', $name) === 1) {
                return $name;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private static function sharedStrings(string $xml): array
    {
        if ($xml === '') {
            return [];
        }

        $document = @simplexml_load_string($xml);

        if ($document === false) {
            return [];
        }

        $strings = [];

        foreach ($document->children(self::NS)->si as $item) {
            $strings[] = self::textOf($item);
        }

        return $strings;
    }

    /**
     * @param  array<int, string>  $shared
     */
    private static function cellValue(SimpleXMLElement $cell, string $type, array $shared): string
    {
        if ($type === 's') {
            $index = (int) $cell->children(self::NS)->v;

            return $shared[$index] ?? '';
        }

        if ($type === 'inlineStr') {
            return self::textOf($cell->children(self::NS)->is);
        }

        return (string) $cell->children(self::NS)->v;
    }

    private static function textOf(?SimpleXMLElement $element): string
    {
        if ($element === null) {
            return '';
        }

        if (isset($element->children(self::NS)->t)) {
            return (string) $element->children(self::NS)->t;
        }

        $text = '';

        foreach ($element->children(self::NS)->r as $run) {
            $text .= (string) $run->children(self::NS)->t;
        }

        return $text;
    }

    private static function columnLetter(int $index): string
    {
        $letters = '';
        $index++;

        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)).$letters;
            $index = intdiv($index, 26);
        }

        return $letters === '' ? 'A' : $letters;
    }

    private static function columnIndex(string $reference): int
    {
        if (preg_match('/^([A-Za-z]+)/', $reference, $matches) !== 1) {
            return 0;
        }

        $index = 0;

        foreach (str_split(strtoupper($matches[1])) as $character) {
            $index = $index * 26 + (ord($character) - 64);
        }

        return max(0, $index - 1);
    }

    private static function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>';
    }

    private static function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private static function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="'.self::NS.'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private static function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'</Relationships>';
    }
}
