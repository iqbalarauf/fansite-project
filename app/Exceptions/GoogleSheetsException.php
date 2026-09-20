<?php

namespace App\Exceptions;

use RuntimeException;

class GoogleSheetsException extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self('Kredensial Service Account Google belum dikonfigurasi. Letakkan file JSON di path yang terdaftar pada GOOGLE_SERVICE_ACCOUNT_PATH.');
    }

    public static function missingSpreadsheet(): self
    {
        return new self('Spreadsheet ID atau nama sheet belum diisi untuk master data ini.');
    }
}
