<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('app_name')->nullable();
                $table->string('sidebar_name')->nullable();
                $table->string('hero_path')->nullable();
                $table->string('login_image_path')->nullable();
                $table->string('app_logo_path')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('app_settings')) {
            Schema::drop('app_settings');
        }
    }
};
