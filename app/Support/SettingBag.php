<?php

namespace App\Support;

use App\Models\AboutSettings;
use App\Models\AppSettings;
use Illuminate\Support\Facades\Cache;

final class SettingBag
{
    /**
     * @return array<string, mixed>
     */
    public static function about(): array
    {
        return Cache::remember('about_settings', 3600, fn (): array => AboutSettings::query()->pluck('value', 'key')->all());
    }

    /**
     * @return array<string, mixed>
     */
    public static function app(): array
    {
        return Cache::remember('app_settings', 3600, fn (): array => AppSettings::query()->pluck('value', 'key')->all());
    }

    /**
     * Determine whether a content feature (e.g. "news", "blog") is enabled.
     * Defaults to enabled when the setting has never been saved.
     */
    public static function featureEnabled(string $feature): bool
    {
        return filter_var(self::app()[$feature.'_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
    }
}
