<?php

// This model was introduced as part of a temporary "settings" feature which
// the project owner decided to revert. The file remains as a harmless
// placeholder to avoid fatal class-not-found errors in environments where
// the feature might still be referenced. Remove this file completely to
// fully delete the settings feature.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Setting extends Model
{
    protected $guarded = [];

    // retrieve a setting by key
    public static function get(string $key, $default = null)
    {
        try {
            $row = DB::table('app_settings')->where('key', $key)->first();

            if (!$row) return $default;

            return $row->value;
        } catch (\Throwable $e) {
            // If table doesn't exist yet (fresh app), return default instead of throwing
            return $default;
        }
    }

    // set or replace a setting
    public static function set(string $key, $value)
    {
        return DB::table('app_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value]
        );
    }

    // get all settings as associative array
    public static function allKeyValues(): array
    {
        try {
            return DB::table('app_settings')->pluck('value', 'key')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
