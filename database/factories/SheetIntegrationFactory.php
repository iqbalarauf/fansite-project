<?php

namespace Database\Factories;

use App\Enums\MasterData;
use App\Enums\SyncMode;
use App\Models\SheetIntegration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SheetIntegration>
 */
class SheetIntegrationFactory extends Factory
{
    protected $model = SheetIntegration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'master_data' => MasterData::ShowTeater,
            'spreadsheet_id' => fake()->uuid(),
            'sheet_name' => 'Sheet1',
            'header_row' => 1,
            'header_column' => 'A',
            'mode' => SyncMode::Manual,
            'auto_sync' => false,
        ];
    }

    public function forMasterData(MasterData $masterData): static
    {
        return $this->state(fn (): array => ['master_data' => $masterData]);
    }

    public function auto(): static
    {
        return $this->state(fn (): array => ['mode' => SyncMode::Auto]);
    }

    public function autoSync(): static
    {
        return $this->state(fn (): array => ['auto_sync' => true]);
    }

    public function disabled(): static
    {
        return $this->state(fn (): array => ['mode' => SyncMode::Disabled]);
    }
}
