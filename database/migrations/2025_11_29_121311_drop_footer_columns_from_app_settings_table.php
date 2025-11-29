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
        if (Schema::hasTable('app_settings')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $footerColumns = [
                    'footer_public_text',
                    'footer_public_copyright',
                    'footer_public_links',
                    'footer_public_social',
                    'footer_auth_text',
                    'footer_auth_copyright',
                    'footer_auth_links',
                    'footer_auth_social',
                ];
                
                foreach ($footerColumns as $col) {
                    if (Schema::hasColumn('app_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('app_settings')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $table->text('footer_public_text')->nullable();
                $table->string('footer_public_copyright', 200)->nullable();
                $table->text('footer_public_links')->nullable();
                $table->text('footer_public_social')->nullable();
                $table->text('footer_auth_text')->nullable();
                $table->string('footer_auth_copyright', 200)->nullable();
                $table->text('footer_auth_links')->nullable();
                $table->text('footer_auth_social')->nullable();
            });
        }
    }
};
