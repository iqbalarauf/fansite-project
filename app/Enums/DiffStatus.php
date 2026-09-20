<?php

namespace App\Enums;

enum DiffStatus: string
{
    case Same = 'same';
    case Different = 'different';
    case OnlyDatabase = 'only_database';
    case OnlySheet = 'only_sheet';

    public function label(): string
    {
        return match ($this) {
            self::Same => 'Sama',
            self::Different => 'Berbeda',
            self::OnlyDatabase => 'Hanya Database',
            self::OnlySheet => 'Hanya Sheet',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Same => 'zinc',
            self::Different => 'amber',
            self::OnlyDatabase => 'blue',
            self::OnlySheet => 'green',
        };
    }

    public function hasConflict(): bool
    {
        return $this !== self::Same;
    }
}
