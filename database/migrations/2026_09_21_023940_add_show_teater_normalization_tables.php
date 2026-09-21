<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Aditif: kolom teks `setlist`/`unit_song` tetap dipertahankan sebagai fallback.
     */
    public function up(): void
    {
        Schema::table('show_teater', function (Blueprint $table) {
            $table->unsignedBigInteger('setlist_id')->nullable()->after('setlist');
            $table->index('setlist_id');
            $table->foreign('setlist_id')->references('id')->on('show_teater_categories')->nullOnDelete();
        });

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
            $table->dropIndex(['setlist_id']);
            $table->dropColumn('setlist_id');
        });
    }
};
