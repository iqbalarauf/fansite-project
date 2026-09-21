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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->index(['parent_id', 'sort_order']);
        });

        Schema::table('live_streaming', function (Blueprint $table) {
            $table->index('live_date');
        });

        Schema::table('timelines', function (Blueprint $table) {
            $table->index('date');
        });

        Schema::table('photobooths', function (Blueprint $table) {
            $table->index(['is_active', 'start_at', 'end_at']);
        });

        Schema::table('news_posts', function (Blueprint $table) {
            $table->index(['status', 'published_at']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex(['parent_id', 'sort_order']);
        });

        Schema::table('live_streaming', function (Blueprint $table) {
            $table->dropIndex(['live_date']);
        });

        Schema::table('timelines', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });

        Schema::table('photobooths', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'start_at', 'end_at']);
        });

        Schema::table('news_posts', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
        });
    }
};
