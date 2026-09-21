<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Konversi waktu antara penyimpanan (UTC) dan zona tampilan pengguna.
 *
 * - Input pengguna (datetime-local, waktu lokal) -> UTC sebelum disimpan.
 * - Timestamp UTC -> zona tampilan sebelum ditampilkan.
 */
final class Timezone
{
    public static function display(): string
    {
        $timezone = (string) config('app.display_timezone', 'Asia/Jakarta');

        return $timezone !== '' ? $timezone : 'Asia/Jakarta';
    }

    public static function nowLocal(): Carbon
    {
        return Carbon::now(self::display());
    }

    /**
     * Tanggal "hari ini" menurut zona tampilan (string Y-m-d).
     */
    public static function today(): string
    {
        return self::nowLocal()->toDateString();
    }

    /**
     * UTC (tersimpan) -> zona tampilan.
     */
    public static function toLocal(CarbonInterface|string|null $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::parse($value)->setTimezone(self::display());
    }

    /**
     * Zona tampilan (input pengguna) -> UTC (untuk disimpan).
     */
    public static function fromLocal(CarbonInterface|string|null $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value, self::display())->utc();
    }

    private static function parse(CarbonInterface|string $value): Carbon
    {
        return $value instanceof CarbonInterface ? Carbon::instance($value) : Carbon::parse($value);
    }
}
