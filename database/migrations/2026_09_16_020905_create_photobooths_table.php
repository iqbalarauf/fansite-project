<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photobooths', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('frame');
            $table->unsignedTinyInteger('columns')->default(2);
            $table->unsignedTinyInteger('rows')->default(3);
            $table->json('slots')->nullable();
            $table->boolean('frame_overlay')->default(false);
            $table->boolean('is_full_open')->default(true);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photobooths');
    }
};
