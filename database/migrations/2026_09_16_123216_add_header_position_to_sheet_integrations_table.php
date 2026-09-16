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
        Schema::table('sheet_integrations', function (Blueprint $table) {
            $table->unsignedInteger('header_row')->default(1)->after('sheet_name');
            $table->string('header_column', 3)->default('A')->after('header_row');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sheet_integrations', function (Blueprint $table) {
            $table->dropColumn(['header_row', 'header_column']);
        });
    }
};
