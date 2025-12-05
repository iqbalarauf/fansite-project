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
        Schema::table('show_teater_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('setlist_id')->nullable()->after('type');
            $table->foreign('setlist_id')->references('id')->on('show_teater_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('show_teater_categories', function (Blueprint $table) {
            $table->dropForeign(['setlist_id']);
            $table->dropColumn('setlist_id');
        });
    }
};
