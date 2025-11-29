<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete all footer-related settings from app_settings table
        DB::table('app_settings')->whereIn('key', [
            'footer_public_show_year',
            'footer_public_text',
            'footer_public_links',
            'footer_auth_show_year',
            'footer_auth_text',
            'footer_auth_links',
        ])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally restore default footer settings
        DB::table('app_settings')->insert([
            ['key' => 'footer_public_show_year', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_public_text', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_public_links', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_auth_show_year', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_auth_text', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_auth_links', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
