<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('app_settings')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $columns = ['app_name', 'sidebar_name', 'hero_path', 'login_image_path', 'app_logo_path'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('app_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('app_settings')) {
            Schema::table('app_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('app_settings', 'app_name')) {
                    $table->string('app_name')->nullable();
                }
                if (!Schema::hasColumn('app_settings', 'sidebar_name')) {
                    $table->string('sidebar_name')->nullable();
                }
                if (!Schema::hasColumn('app_settings', 'hero_path')) {
                    $table->string('hero_path')->nullable();
                }
                if (!Schema::hasColumn('app_settings', 'login_image_path')) {
                    $table->string('login_image_path')->nullable();
                }
                if (!Schema::hasColumn('app_settings', 'app_logo_path')) {
                    $table->string('app_logo_path')->nullable();
                }
            });
        }
    }
};
