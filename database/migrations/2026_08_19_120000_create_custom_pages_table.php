<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('custom_pages')) {
            return;
        }

        Schema::create('custom_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('draft')->index();
            $table->json('blocks');
            $table->string('display_mode')->default('full');
            $table->string('background_color')->default('slate');
            $table->string('title_alignment')->default('left');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_pages');
    }
};
