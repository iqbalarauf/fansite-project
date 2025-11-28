<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
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

    public function down(): void
    {
        if (Schema::hasTable('app_settings')) {
            Schema::table('app_settings', function (Blueprint $table) {
                if (Schema::hasColumn('app_settings', 'app_name')) {
                    $table->dropColumn('app_name');
                }
                if (Schema::hasColumn('app_settings', 'sidebar_name')) {
                    $table->dropColumn('sidebar_name');
                }
                if (Schema::hasColumn('app_settings', 'hero_path')) {
                    $table->dropColumn('hero_path');
                }
                if (Schema::hasColumn('app_settings', 'login_image_path')) {
                    $table->dropColumn('login_image_path');
                }
                if (Schema::hasColumn('app_settings', 'app_logo_path')) {
                    $table->dropColumn('app_logo_path');
                }
            });
        }
    }
};
