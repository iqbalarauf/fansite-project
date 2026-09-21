<?php

namespace App\Models;

use App\Enums\MasterData;
use App\Enums\SyncMode;
use Database\Factories\SheetIntegrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheetIntegration extends Model
{
    /** @use HasFactory<SheetIntegrationFactory> */
    use HasFactory;

    protected $fillable = [
        'master_data',
        'spreadsheet_id',
        'sheet_name',
        'header_row',
        'header_column',
        'mode',
        'auto_sync',
        'auto_direction',
        'last_synced_at',
        'last_sync_direction',
    ];

    protected $casts = [
        'master_data' => MasterData::class,
        'mode' => SyncMode::class,
        'auto_sync' => 'boolean',
        'header_row' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    public function isConfigured(): bool
    {
        return filled($this->spreadsheet_id) && filled($this->sheet_name);
    }

    public function markSynced(string $direction): void
    {
        $this->forceFill([
            'last_synced_at' => now(),
            'last_sync_direction' => $direction,
        ])->save();
    }
}
