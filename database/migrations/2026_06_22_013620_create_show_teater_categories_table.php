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

            $table->foreign('setlist_id')->references('id')->on('show_teater_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('show_teater_categories');
    }
};
