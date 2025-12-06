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
        // Add indexes to posts table for better query performance
        Schema::table('posts', function (Blueprint $table) {
            $table->index('status', 'posts_status_index');
            $table->index('published_at', 'posts_published_at_index');
            $table->index('category', 'posts_category_index');
            // Composite index for common query: WHERE status = 'published' ORDER BY published_at
            $table->index(['status', 'published_at'], 'posts_status_published_at_index');
        });

        // Add indexes to custom_pages table
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->index('status', 'custom_pages_status_index');
            $table->index('published_at', 'custom_pages_published_at_index');
            $table->index(['status', 'published_at'], 'custom_pages_status_published_at_index');
        });

        // Add indexes to gallery table
        Schema::table('gallery', function (Blueprint $table) {
            $table->index('order', 'gallery_order_index');
            $table->index('is_published', 'gallery_is_published_index');
            $table->index('type', 'gallery_type_index');
            // Composite index for: WHERE type = 'photo' AND is_published = true ORDER BY order
            $table->index(['type', 'is_published', 'order'], 'gallery_type_published_order_index');
        });

        // Add indexes to show_teater table
        Schema::table('show_teater', function (Blueprint $table) {
            $table->index('show_date', 'show_teater_show_date_index');
            $table->index('setlist', 'show_teater_setlist_index');
            $table->index('unit_song', 'show_teater_unit_song_index');
        });

        // Add indexes to concert_events table
        Schema::table('concert_events', function (Blueprint $table) {
            $table->index('event_date', 'concert_events_event_date_index');
            $table->index('status', 'concert_events_status_index');
            $table->index(['status', 'event_date'], 'concert_events_status_date_index');
        });

        // Add indexes to meet_greet_events table
        Schema::table('meet_greet_events', function (Blueprint $table) {
            $table->index('event_date', 'meet_greet_events_event_date_index');
            $table->index('event_type', 'meet_greet_events_event_type_index');
        });

        // Add indexes to live_streaming table
        Schema::table('live_streaming', function (Blueprint $table) {
            $table->index('live_date', 'live_streaming_live_date_index');
            $table->index('platform', 'live_streaming_platform_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_status_index');
            $table->dropIndex('posts_published_at_index');
            $table->dropIndex('posts_category_index');
            $table->dropIndex('posts_status_published_at_index');
        });

        Schema::table('custom_pages', function (Blueprint $table) {
            $table->dropIndex('custom_pages_status_index');
            $table->dropIndex('custom_pages_published_at_index');
            $table->dropIndex('custom_pages_status_published_at_index');
        });

        Schema::table('gallery', function (Blueprint $table) {
            $table->dropIndex('gallery_order_index');
            $table->dropIndex('gallery_is_published_index');
            $table->dropIndex('gallery_type_index');
            $table->dropIndex('gallery_type_published_order_index');
        });

        Schema::table('show_teater', function (Blueprint $table) {
            $table->dropIndex('show_teater_show_date_index');
            $table->dropIndex('show_teater_setlist_index');
            $table->dropIndex('show_teater_unit_song_index');
        });

        Schema::table('concert_events', function (Blueprint $table) {
            $table->dropIndex('concert_events_event_date_index');
            $table->dropIndex('concert_events_status_index');
            $table->dropIndex('concert_events_status_date_index');
        });

        Schema::table('meet_greet_events', function (Blueprint $table) {
            $table->dropIndex('meet_greet_events_event_date_index');
            $table->dropIndex('meet_greet_events_event_type_index');
        });

        Schema::table('live_streaming', function (Blueprint $table) {
            $table->dropIndex('live_streaming_live_date_index');
            $table->dropIndex('live_streaming_platform_index');
        });
    }
};
