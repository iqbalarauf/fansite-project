<?php

namespace App\Support;

use App\Models\ShowTeaterCategories;
use Illuminate\Support\Str;

/**
 * Menyelesaikan nama setlist / unit song menjadi id kategori.
 *
 * Pencocokan memakai `name` ATAU `jp_name`, tidak membedakan besar-kecil huruf
 * dan spasi berlebih. Unit song di-scope ke setlist-nya lebih dulu, lalu global.
 */
final class ShowTeaterCategoryResolver
{
    /** @var array<string, int> */
    private array $setlistByName = [];

    /** @var array<string, int> */
    private array $setlistByJp = [];

    /** @var array<int, array<string, int>> */
    private array $unitByName = [];

    /** @var array<int, array<string, int>> */
    private array $unitByJp = [];

    public function __construct()
    {
        $categories = ShowTeaterCategories::query()->get(['id', 'type', 'name', 'jp_name', 'setlist_id']);

        foreach ($categories as $category) {
            $nameKey = self::normalize((string) $category->name);
            $jpKey = self::normalize((string) $category->jp_name);

            if ($category->type === ShowTeaterCategories::TYPE_SETLIST) {
                $this->setlistByName[$nameKey] = (int) $category->id;

                if ($jpKey !== '') {
                    $this->setlistByJp[$jpKey] = (int) $category->id;
                }

                continue;
            }

            if ($category->type !== ShowTeaterCategories::TYPE_UNIT_SONG) {
                continue;
            }

            $setlistId = (int) $category->setlist_id;
            $this->unitByName[$setlistId][$nameKey] = (int) $category->id;

            if ($jpKey !== '') {
                $this->unitByJp[$setlistId][$jpKey] = (int) $category->id;
            }
        }
    }

    public function setlistId(string $name): ?int
    {
        $key = self::normalize($name);

        return $this->setlistByName[$key] ?? $this->setlistByJp[$key] ?? null;
    }

    public function unitSongId(?int $setlistId, string $token): ?int
    {
        $key = self::normalize($token);

        if ($setlistId !== null) {
            $scoped = $this->unitByName[$setlistId][$key] ?? $this->unitByJp[$setlistId][$key] ?? null;

            if ($scoped !== null) {
                return $scoped;
            }
        }

        foreach ($this->unitByName as $entries) {
            if (isset($entries[$key])) {
                return $entries[$key];
            }
        }

        foreach ($this->unitByJp as $entries) {
            if (isset($entries[$key])) {
                return $entries[$key];
            }
        }

        return null;
    }

    public static function normalize(string $value): string
    {
        return Str::lower(trim(preg_replace('/\s+/u', ' ', $value) ?? ''));
    }

    /**
     * @return array<int, string>
     */
    public static function splitUnitSongs(string $value): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\s*;\s*/', $value) ?: []),
            static fn (string $song): bool => $song !== '',
        ));
    }
}
