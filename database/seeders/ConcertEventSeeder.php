<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConcertEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'event_name' => 'JKT48 13th Anniversary Concert',
                'event_date' => '2024-12-17',
                'location' => 'Tennis Indoor Senayan, Jakarta',
                'status' => 'off-air',
                'purchase_link' => 'https://www.jkt48.com/tickets',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'JKT48 at Java Jazz Festival 2025',
                'event_date' => '2025-03-15',
                'location' => 'JIExpo Kemayoran, Jakarta',
                'status' => 'on-air',
                'purchase_link' => 'https://www.javajazzfestival.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'JKT48 Handshake Festival Surabaya',
                'event_date' => '2025-02-20',
                'location' => 'Grand City Surabaya',
                'status' => 'on-air',
                'purchase_link' => 'https://www.jkt48.com/events/surabaya',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'Meet & Greet Bandung',
                'event_date' => '2025-01-28',
                'location' => 'Trans Studio Mall Bandung',
                'status' => 'off-air',
                'purchase_link' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'JKT48 Countdown Festival 2025',
                'event_date' => '2024-12-31',
                'location' => 'ICE BSD City, Tangerang',
                'status' => 'off-air',
                'purchase_link' => 'https://www.jkt48.com/countdown2025',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('concert_events')->insert($events);
    }
}
