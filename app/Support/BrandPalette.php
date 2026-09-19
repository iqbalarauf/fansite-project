<?php

namespace App\Support;

final class BrandPalette
{
    public const PRIMARY = '#6C7CE8';

    public const SECONDARY = '#A5B4FC';

    public const TERTIARY = '#FFD166';

    /**
     * @param  mixed  $value
     */
    public static function normalize($value, string $fallback): string
    {
        $value = (string) $value;

        return preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) === 1
            ? strtoupper($value)
            : $fallback;
    }
}
