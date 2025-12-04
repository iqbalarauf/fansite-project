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
            $table->string('setlist', 32);
            $table->string('unit_song', 29);
            $table->integer('is_global_center')->nullable();
            $table->integer('is_us_center')->nullable();
            $table->string('is_the_show_has_event', 41)->nullable();
            $table->string('additional_information', 48)->nullable();
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
