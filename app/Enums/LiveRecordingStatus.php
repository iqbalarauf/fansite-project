<?php

namespace App\Enums;

enum LiveRecordingStatus: string
{
    case Starting = 'starting';
    case Recording = 'recording';
    case Stopping = 'stopping';
    case Finalizing = 'finalizing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Starting => 'Menyiapkan',
            self::Recording => 'Merekam',
            self::Stopping => 'Menghentikan',
            self::Finalizing => 'Finalisasi',
            self::Completed => 'Selesai',
            self::Failed => 'Gagal',
        };
    }

    /**
     * Statuses that still have (or are about to have) an active ffmpeg process.
     */
    public function isActive(): bool
    {
        return in_array($this, [self::Starting, self::Recording, self::Stopping], true);
    }

    /**
     * Whether the final MP4 file is ready to be previewed or downloaded.
     */
    public function isPlayable(): bool
    {
        return $this === self::Completed;
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Completed => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
            self::Failed => 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300',
            self::Recording => 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-300',
            self::Starting, self::Stopping, self::Finalizing => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-300',
        };
    }
}
