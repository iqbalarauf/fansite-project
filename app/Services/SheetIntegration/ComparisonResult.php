<?php

namespace App\Services\SheetIntegration;

use App\Enums\DiffStatus;
use App\Enums\MasterData;

final class ComparisonResult
{
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, DiffRow>  $rows
     */
    public function __construct(
        public readonly MasterData $masterData,
        public readonly array $headers,
        public readonly array $rows,
    ) {}

    public function conflictCount(): int
    {
        return count(array_filter(
            $this->rows,
            static fn (DiffRow $row): bool => $row->status->hasConflict(),
        ));
    }

    public function hasConflicts(): bool
    {
        return $this->conflictCount() > 0;
    }

    /**
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $counts = array_fill_keys(
            array_map(static fn (DiffStatus $status): string => $status->value, DiffStatus::cases()),
            0,
        );

        foreach ($this->rows as $row) {
            $counts[$row->status->value]++;
        }

        return $counts;
    }
}
