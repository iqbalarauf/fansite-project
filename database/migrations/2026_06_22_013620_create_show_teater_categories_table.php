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
        Schema::create('show_teater_categories', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // 'setlist' or 'unit_song'
            $table->string('name', 100);
            $table->string('jp_name', 100)->nullable();
            $table->unsignedBigInteger('setlist_id')->nullable(); // only for unit_song
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->index('type');
            $table->foreign('setlist_id')->references('id')->on('show_teater_categories')->onDelete('cascade');
        });

        // FK show_teater.setlist_id (dibuat di sini karena show_teater_categories baru tersedia).
        Schema::table('show_teater', function (Blueprint $table) {
            $table->foreign('setlist_id')->references('id')->on('show_teater_categories')->nullOnDelete();
        });

        // Pivot unit song (mendukung double US).
        Schema::create('show_teater_unit_song', function (Blueprint $table) {
            $table->id();
            $table->integer('show_id');
            $table->unsignedBigInteger('show_teater_categories_id');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['show_id', 'show_teater_categories_id']);
            $table->index('show_id');

            $table->foreign('show_id')->references('show_id')->on('show_teater')->cascadeOnDelete();
            $table->foreign('show_teater_categories_id')->references('id')->on('show_teater_categories')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('show_teater_unit_song');

        Schema::table('show_teater', function (Blueprint $table) {
            $table->dropForeign(['setlist_id']);
        });

        Schema::dropIfExists('show_teater_categories');
    }
};
