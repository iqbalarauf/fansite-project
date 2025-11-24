<?php

// This model was introduced as part of a temporary "settings" feature which
// the project owner decided to revert. The file remains as a harmless
// placeholder to avoid fatal class-not-found errors in environments where
// the feature might still be referenced. Remove this file completely to
// fully delete the settings feature.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    public static function get(string $key, $default = null)
    {
        return $default;
    }

    public static function set(string $key, $value)
    {
        return null;
    }
}
