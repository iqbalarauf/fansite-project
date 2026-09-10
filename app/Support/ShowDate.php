<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class ShowDate
{
    public static function normalize(string $date): string
    {
        return Carbon::parse(str_replace('/', '-', $date))->format('Y-m-d');
    }

    public static function sqlExpression(string $column = 'show_date'): string
    {
        return "REPLACE({$column}, '/', '-')";
    }
}
