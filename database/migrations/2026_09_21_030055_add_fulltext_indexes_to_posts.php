<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Hanya berlaku di MySQL; di driver lain dilewati agar tetap kompatibel.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE news_posts ADD FULLTEXT news_posts_title_excerpt_fulltext (title, excerpt)');
        DB::statement('ALTER TABLE blog_posts ADD FULLTEXT blog_posts_title_excerpt_fulltext (title, excerpt)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE news_posts DROP INDEX news_posts_title_excerpt_fulltext');
        DB::statement('ALTER TABLE blog_posts DROP INDEX blog_posts_title_excerpt_fulltext');
    }
};
