<?php

namespace App\Services\SheetIntegration;

use App\Enums\DiffStatus;

final class DiffRow
{
    /**
     * @param  array<string, string|null>|null  $database
     * @param  array<string, string|null>|null  $sheet
     * @param  array<string, array{database: string|null, sheet: string|null}>  $differences
     */
    public function __construct(
        public readonly string $key,
        public readonly DiffStatus $status,
        public readonly ?array $database,
        public readonly ?array $sheet,
        public readonly array $differences = [],
    ) {}

    public function isNewFromSheet(): bool
    {
        return str_starts_with($this->key, 'new:');
    }

    /**
     * @return array{database: string|null, sheet: string|null}|null
     */
    public function difference(string $column): ?array
    {
        return $this->differences[$column] ?? null;
    }
}
