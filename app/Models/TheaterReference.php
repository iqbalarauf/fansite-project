<?php

namespace App\Models;

use App\Support\ShowDate;
use App\Support\Timezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TheaterReference extends Model
{
    protected $fillable = ['reference_code', 'month', 'year', 'processed_at'];

    /**
     * Hapus reference dari bulan sebelumnya.
     *
     * Reference yang masih dipakai oleh show_teater dengan tanggal belum terlewat
     * TIDAK dihapus, agar reference_code pada show_teater tetap tersedia.
     */
    public static function deleteOldReferences(int $currentMonth, int $currentYear): void
    {
        $showDateExpression = ShowDate::sqlExpression();
        $today = Timezone::today();

        $protectedCodes = DB::table('show_teater')
            ->whereNull('deleted_at')
            ->whereNotNull('reference_code')
            ->whereRaw("{$showDateExpression} >= ?", [$today])
            ->pluck('reference_code');

        self::query()
            ->where(function ($query) use ($currentMonth, $currentYear): void {
                $query->where('month', '!=', $currentMonth)
                    ->orWhere('year', '!=', $currentYear);
            })
            ->when(
                $protectedCodes->isNotEmpty(),
                fn ($query) => $query->whereNotIn('reference_code', $protectedCodes),
            )
            ->delete();
    }
}
