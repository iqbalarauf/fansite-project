<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix data types in show_teater table:
     * - Convert string show_date to date type
     * - Convert integer is_global_center to boolean
     * - Convert integer is_us_center to boolean
     */
    public function up(): void
    {
        Schema::table('show_teater', function (Blueprint $table) {
            // Note: This migration assumes show_date is in a parseable format
            // You may need to clean/convert the data first before changing the type

            // For now, we'll keep show_date as string since it might contain
            // various formats. Consider data cleanup first.
            // $table->date('show_date')->change();

            // Convert boolean fields
            $table->boolean('is_global_center')->nullable()->default(false)->change();
            $table->boolean('is_us_center')->nullable()->default(false)->change();
        });

        // Update existing integer values to boolean
        DB::statement('UPDATE show_teater SET is_global_center = CASE WHEN is_global_center = 1 THEN true ELSE false END');
        DB::statement('UPDATE show_teater SET is_us_center = CASE WHEN is_us_center = 1 THEN true ELSE false END');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('show_teater', function (Blueprint $table) {
            // Revert back to integer
            $table->integer('is_global_center')->nullable()->change();
            $table->integer('is_us_center')->nullable()->change();
        });
    }
};
