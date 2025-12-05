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
        Schema::create('meet_greet_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->enum('event_type', ['meet-greet', 'video-call'])->default('meet-greet');
            $table->date('event_date');
            $table->date('event_date_2')->nullable(); // For video call second date
            $table->datetime('ticket_sale_datetime')->nullable(); // Jadwal pembelian tiket
            $table->string('purchase_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meet_greet_events');
    }
};
