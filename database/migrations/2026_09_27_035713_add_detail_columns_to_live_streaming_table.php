<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_streaming', function (Blueprint $table): void {
            if (! Schema::hasColumn('live_streaming', 'start_time')) {
                $table->dateTime('start_time')->nullable()->after('live_date');
            }

            if (! Schema::hasColumn('live_streaming', 'end_time')) {
                $table->dateTime('end_time')->nullable()->after('start_time');
            }

            if (! Schema::hasColumn('live_streaming', 'max_viewers')) {
                $table->unsignedInteger('max_viewers')->nullable()->after('duration');
            }

            if (! Schema::hasColumn('live_streaming', 'comment_count')) {
                $table->unsignedInteger('comment_count')->nullable()->after('max_viewers');
            }

            if (! Schema::hasColumn('live_streaming', 'gift_count')) {
                $table->unsignedInteger('gift_count')->nullable()->after('comment_count');
            }

            if (! Schema::hasColumn('live_streaming', 'total_gold')) {
                $table->unsignedBigInteger('total_gold')->nullable()->after('gift_count');
            }

            if (! Schema::hasColumn('live_streaming', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('total_gold');
            }

            if (! Schema::hasColumn('live_streaming', 'gifts')) {
                $table->json('gifts')->nullable()->after('youtube_url');
            }

            if (! Schema::hasColumn('live_streaming', 'top_senders')) {
                $table->json('top_senders')->nullable()->after('gifts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_streaming', function (Blueprint $table): void {
            foreach (['start_time', 'end_time', 'max_viewers', 'comment_count', 'gift_count', 'total_gold', 'youtube_url', 'gifts', 'top_senders'] as $column) {
                if (Schema::hasColumn('live_streaming', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
