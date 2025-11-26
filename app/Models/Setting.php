<?php

// This model was introduced as part of a temporary "settings" feature which
// the project owner decided to revert. The file remains as a harmless
// placeholder to avoid fatal class-not-found errors in environments where
// the feature might still be referenced. Remove this file completely to
// fully delete the settings feature.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $guarded = [];

    // retrieve a setting by key
    public static function get(string $key, $default = null)
    {
        if (!self::tableReady()) {
            return $default;
        }

        try {
            $row = DB::table('app_settings')->where('key', $key)->first();

            if (!$row) return $default;

            return $row->value;
        } catch (\Throwable $e) {
            // If table doesn't exist yet (fresh app), return default instead of throwing
            return $default;
        }
    }

    /**
     * Persist or update a single setting value.
     *
     * Strings are trimmed before saving so that clearing an input truly removes
     * the value (empty strings are stored as null). We also guard against the
     * table not being available yet so the rest of the app can continue to run
     * without throwing an exception.
     */
    public static function set(string $key, $value): bool
    {
        if (!self::tableReady()) {
            return false;
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '') {
            $value = null;
        }

        try {
            $updated = DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value]
            );

            self::syncRuntimeConfig($key, $value);

            return (bool) $updated;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // get all settings as associative array
    public static function allKeyValues(): array
    {
        try {
            if (!self::tableReady()) {
                return [];
            }

            return DB::table('app_settings')->pluck('value', 'key')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Keep runtime config() values aligned with the latest stored settings so
     * pages rendered during the same request can immediately read the change.
     */
    protected static function syncRuntimeConfig(string $key, $value): void
    {
        if ($key === 'app_name' && $value !== null) {
            config(['app.name' => $value]);
        }

        if ($key === 'sidebar_name') {
            config(['app.sidebar_name' => $value]);
        }
    }

    protected static function tableReady(): bool
    {
        static $hasTable = null;

        if ($hasTable !== null) {
            return $hasTable;
        }

        try {
            return $hasTable = Schema::hasTable('app_settings');
        } catch (\Throwable $e) {
            return $hasTable = false;
        }
    }
}
