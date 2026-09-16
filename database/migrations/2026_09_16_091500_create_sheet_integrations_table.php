<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sheet_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('master_data')->unique();
            $table->string('spreadsheet_id')->nullable();
            $table->string('sheet_name')->nullable();
            $table->string('mode')->default('disabled');
            $table->string('auto_direction')->default('database_to_sheet');
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_direction')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sheet_integrations');
    }
};
