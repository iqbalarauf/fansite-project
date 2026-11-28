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
        Schema::create('live_recordings', function (Blueprint $table): void {
            $table->id();
            $table->string('live_slug')->unique();
            $table->string('platform');
            $table->string('member_name');
            $table->string('url_key')->nullable();
            $table->text('source_url');
            $table->string('status')->default('starting')->index();
            $table->string('file_path')->nullable()->comment('Final MP4 path relative to the recordings disk');
            $table->string('segment_path')->nullable()->comment('Temporary MPEG-TS path relative to the recordings disk');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('pid')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('last_seen_live_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('started_at');
            $table->index('last_seen_live_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_recordings');
    }
};
