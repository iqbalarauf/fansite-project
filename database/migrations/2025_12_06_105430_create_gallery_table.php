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
        Schema::create('gallery', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['photo', 'video'])->default('photo');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('credit')->nullable();
            $table->string('image_path')->nullable(); // for photo type
            $table->string('video_url')->nullable(); // for video type (YouTube URL)
            $table->integer('order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery');
    }
};
