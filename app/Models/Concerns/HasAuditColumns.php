<?php

namespace App\Models\Concerns;

/**
 * Mengisi kolom audit `created_by`/`updated_by` dari pengguna terautentikasi.
 */
trait HasAuditColumns
{
    protected static function bootHasAuditColumns(): void
    {
        static::creating(function ($model): void {
            if (auth()->check()) {
                $model->created_by ??= auth()->id();
                $model->updated_by ??= auth()->id();
            }
        });

        static::updating(function ($model): void {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
