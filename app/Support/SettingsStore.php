<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Single place responsible for persisting application and about settings.
 */
final class SettingsStore
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public static function setApp(array $settings): void
    {
        foreach ($settings as $key => $value) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()],
            );
        }

        Cache::forget('app_settings');
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public static function setAbout(array $settings): void
    {
        $now = now();
        $rows = collect($settings)
            ->map(fn ($value, $key) => [
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        DB::table('about_settings')->upsert($rows, ['key'], ['value', 'updated_at']);

        Cache::forget('about_settings');
    }
}
