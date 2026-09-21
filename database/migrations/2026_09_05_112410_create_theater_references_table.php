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
        Schema::create('theater_references', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code')->unique();
            $table->integer('month');
            $table->integer('year');
            $table->timestamps();
            $table->timestamp('processed_at')->nullable();
        });

        // FK show_teater.reference_code -> theater_references.reference_code.
        // nullOnDelete: menghapus reference tidak menghapus show-nya.
        Schema::table('show_teater', function (Blueprint $table) {
            $table->foreign('reference_code')->references('reference_code')->on('theater_references')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('show_teater', function (Blueprint $table) {
            $table->dropForeign(['reference_code']);
        });

        Schema::dropIfExists('theater_references');
    }
};
