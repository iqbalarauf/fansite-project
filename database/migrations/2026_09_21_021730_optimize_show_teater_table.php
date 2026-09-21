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
        Schema::table('show_teater', function (Blueprint $table) {
            $table->tinyInteger('is_global_center')->nullable()->change();
            $table->tinyInteger('is_us_center')->nullable()->change();
            $table->tinyInteger('is_scraped_data')->nullable()->change();
            $table->tinyInteger('is_member_show')->nullable()->change();
        });

        Schema::table('show_teater', function (Blueprint $table) {
            $table->index('show_date');
            $table->index('setlist');
            $table->index('unit_song');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('show_teater', function (Blueprint $table) {
            $table->dropIndex(['show_date']);
            $table->dropIndex(['setlist']);
            $table->dropIndex(['unit_song']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('show_teater', function (Blueprint $table) {
            $table->integer('is_global_center')->nullable()->change();
            $table->integer('is_us_center')->nullable()->change();
            $table->integer('is_scraped_data')->nullable()->change();
            $table->integer('is_member_show')->nullable()->change();
        });
    }
};
