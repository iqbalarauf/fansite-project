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
        Schema::create('live_streaming', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['IDN App', 'Showroom']);
            $table->date('live_date');
            $table->integer('duration')->nullable()->comment('Duration in minutes');
            $table->text('additional_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_streaming');
    }
};
