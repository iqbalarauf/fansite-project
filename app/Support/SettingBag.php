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
}
