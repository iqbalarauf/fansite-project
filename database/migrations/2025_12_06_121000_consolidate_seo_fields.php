<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Consolidate SEO fields to use consistent naming (meta_*)
     * Remove duplicate seo_* fields in favor of meta_* fields
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Copy data from seo_* to meta_* if meta_* is empty
            DB::statement('UPDATE posts SET meta_title = seo_title WHERE meta_title IS NULL AND seo_title IS NOT NULL');
            DB::statement('UPDATE posts SET meta_description = seo_description WHERE meta_description IS NULL AND seo_description IS NOT NULL');

            // Drop duplicate seo_* columns
            $table->dropColumn(['seo_title', 'seo_description']);
        });

        Schema::table('custom_pages', function (Blueprint $table) {
            // Check if seo_* columns exist first
            if (Schema::hasColumn('custom_pages', 'seo_title')) {
                DB::statement('UPDATE custom_pages SET meta_title = seo_title WHERE meta_title IS NULL AND seo_title IS NOT NULL');
                $table->dropColumn('seo_title');
            }

            if (Schema::hasColumn('custom_pages', 'seo_description')) {
                DB::statement('UPDATE custom_pages SET meta_description = seo_description WHERE meta_description IS NULL AND seo_description IS NOT NULL');
                $table->dropColumn('seo_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('body');
            $table->text('seo_description')->nullable()->after('seo_title');

            // Copy data back
            DB::statement('UPDATE posts SET seo_title = meta_title WHERE seo_title IS NULL');
            DB::statement('UPDATE posts SET seo_description = meta_description WHERE seo_description IS NULL');
        });

        Schema::table('custom_pages', function (Blueprint $table) {
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
        });
    }
};
