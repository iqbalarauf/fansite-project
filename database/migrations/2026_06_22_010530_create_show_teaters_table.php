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
        Schema::create('show_teater', function (Blueprint $table) {
            $table->integer('show_id')->primary();
            $table->string('show_date', 25);
            $table->string('setlist', 128);
            $table->unsignedBigInteger('setlist_id')->nullable();
            $table->string('unit_song', 128)->nullable();
            $table->tinyInteger('is_global_center')->nullable();
            $table->tinyInteger('is_us_center')->nullable();
            $table->string('is_the_show_has_event', 255)->nullable();
            $table->string('additional_information', 255)->nullable();
            $table->string('reference_code', 255)->nullable();
            $table->tinyInteger('is_scraped_data')->nullable();
            $table->tinyInteger('is_member_show')->nullable();
            $table->timestamp('last_fetch_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

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
        Schema::dropIfExists('show_teater');
    }
};
