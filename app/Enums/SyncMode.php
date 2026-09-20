<?php

namespace App\Enums;

enum SyncMode: string
{
    case Disabled = 'disabled';
    case Manual = 'manual';
    case Auto = 'auto';

    public function label(): string
    {
        return match ($this) {
            self::Disabled => 'Nonaktif',
            self::Manual => 'Manual',
            self::Auto => 'Auto-Sync',
        };
    }

    public function isAuto(): bool
    {
        return $this === self::Auto;
    }

    public function isEnabled(): bool
    {
        return $this !== self::Disabled;
    }
}
