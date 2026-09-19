<?php

namespace App\Support;

final class TextLines
{
    /**
     * Split a multi-line textarea value into trimmed non-empty lines.
     *
     * @return list<string>
     */
    public static function parse(mixed $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter(fn (string $line): bool => $line !== '')
            ->values()
            ->all();
    }
}
