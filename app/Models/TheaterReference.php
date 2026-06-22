<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TheaterReference extends Model
{
    protected $fillable = ['reference_code', 'month', 'year', 'processed_at'];

    public static function deleteOldReferences($currentMonth, $currentYear)
    {
        // Hapus reference_code dari bulan sebelumnya
        self::where('month', '!=', $currentMonth)
            ->orWhere('year', '!=', $currentYear)
            ->delete();
    }
}
