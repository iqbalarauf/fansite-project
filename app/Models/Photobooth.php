<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'frame', 'columns', 'rows', 'slots', 'frame_overlay', 'is_full_open', 'start_at', 'end_at', 'is_active'])]
class Photobooth extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'columns' => 'integer',
            'rows' => 'integer',
            'slots' => 'array',
            'frame_overlay' => 'boolean',
            'is_full_open' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public static function current(): ?self
    {
        return static::query()->latest('id')->first();
    }

    public function poseCount(): int
    {
        return max(1, $this->columns * $this->rows);
    }

    /**
     * @return array<int, array{x: float, y: float, width: float, height: float}>
     */
    public function resolvedSlots(): array
    {
        $stored = $this->slots;

        if (is_array($stored) && $stored !== []) {
            return array_values(array_map(fn (array $slot): array => [
                'x' => round((float) ($slot['x'] ?? 0), 2),
                'y' => round((float) ($slot['y'] ?? 0), 2),
                'width' => round((float) ($slot['width'] ?? 0), 2),
                'height' => round((float) ($slot['height'] ?? 0), 2),
            ], $stored));
        }

        return $this->defaultSlots();
    }

    /**
     * @return array<int, array{x: float, y: float, width: float, height: float}>
     */
    public function defaultSlots(float $insetRatio = 0.06): array
    {
        $columns = max(1, $this->columns);
        $rows = max(1, $this->rows);

        $cellWidth = 100 / $columns;
        $cellHeight = 100 / $rows;
        $insetX = $cellWidth * $insetRatio;
        $insetY = $cellHeight * $insetRatio;

        $slots = [];

        for ($index = 0; $index < $columns * $rows; $index++) {
            $column = $index % $columns;
            $row = intdiv($index, $columns);

            $slots[] = [
                'x' => round(($column * $cellWidth) + $insetX, 2),
                'y' => round(($row * $cellHeight) + $insetY, 2),
                'width' => round($cellWidth - ($insetX * 2), 2),
                'height' => round($cellHeight - ($insetY * 2), 2),
            ];
        }

        return $slots;
    }
}
