<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `concert_events` MODIFY COLUMN `status` ENUM('off-air', 'on-air', 'jkt48-event', 'media', 'ofc-event', 'brand') NOT NULL DEFAULT 'off-air'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `concert_events` MODIFY COLUMN `status` ENUM('off-air', 'on-air') NOT NULL DEFAULT 'off-air'");
    }
};
