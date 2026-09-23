<?php

namespace App\Support;

final class CustomPageTheme
{
    /**
     * Warna judul halaman agar kontras dengan background (hex) halaman.
     *
     * Mengembalikan kode hex, atau null bila cukup memakai class default
     * `text-slate-900 dark:text-white` (preset/transparent).
     */
    public static function titleColor(?string $background): ?string
    {
        $background = trim((string) $background);

        if (preg_match('/^#([0-9A-Fa-f]{6})$/', $background, $matches) !== 1) {
            return null;
        }

        $hex = $matches[1];
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        $luminance = (0.2126 * $red + 0.7152 * $green + 0.0722 * $blue) / 255;

        return $luminance > 0.6 ? '#0F172A' : '#F8FAFC';
    }
}
