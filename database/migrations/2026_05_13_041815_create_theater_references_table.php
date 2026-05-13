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
            $table->timestamp('processed_at')->nullable()->after('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theater_references');
    }
};
