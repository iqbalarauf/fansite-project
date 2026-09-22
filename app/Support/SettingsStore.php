<?php

namespace App\Support;

use App\Models\AboutSettings;
use App\Models\AppSettings;
use Illuminate\Support\Facades\Cache;

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
            AppSettings::query()->updateOrInsert(
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

        AboutSettings::query()->upsert($rows, ['key'], ['value', 'updated_at']);

        Cache::forget('about_settings');
    }
}
