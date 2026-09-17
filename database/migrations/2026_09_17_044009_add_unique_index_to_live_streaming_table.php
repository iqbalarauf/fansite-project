<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->removeDuplicateLiveIds();

        if ($this->hasUniqueLiveIdIndex()) {
            return;
        }

        Schema::table('live_streaming', function (Blueprint $table): void {
            $table->unique('live_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->hasUniqueLiveIdIndex()) {
            return;
        }

        Schema::table('live_streaming', function (Blueprint $table): void {
            $table->dropUnique(['live_id']);
        });
    }

    /**
     * Keep the earliest row for each duplicated live_id so the unique index can be created.
     */
    private function removeDuplicateLiveIds(): void
    {
        $duplicatedLiveIds = DB::table('live_streaming')
            ->select('live_id')
            ->whereNotNull('live_id')
            ->groupBy('live_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('live_id');

        foreach ($duplicatedLiveIds as $liveId) {
            $keepId = DB::table('live_streaming')->where('live_id', $liveId)->min('id');

            DB::table('live_streaming')
                ->where('live_id', $liveId)
                ->where('id', '<>', $keepId)
                ->delete();
        }
    }

    private function hasUniqueLiveIdIndex(): bool
    {
        foreach (Schema::getIndexes('live_streaming') as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === ['live_id']) {
                return true;
            }
        }

        return false;
    }
};
