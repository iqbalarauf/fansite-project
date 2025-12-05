<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeetGreetEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'event_name' => 'JKT48 National Handshake Festival',
                'event_type' => 'meet-greet',
                'event_date' => '2025-01-15',
                'event_date_2' => null,
                'ticket_sale_datetime' => '2025-01-01 10:00:00',
                'purchase_link' => 'https://www.jkt48.com/handshake/national',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'Member Video Call Session - January',
                'event_type' => 'video-call',
                'event_date' => '2025-01-20',
                'event_date_2' => '2025-01-21',
                'ticket_sale_datetime' => '2025-01-05 12:00:00',
                'purchase_link' => 'https://www.jkt48.com/videocall/january',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'Mini Handshake Event Jakarta',
                'event_type' => 'meet-greet',
                'event_date' => '2025-02-05',
                'event_date_2' => null,
                'ticket_sale_datetime' => '2025-01-20 09:00:00',
                'purchase_link' => 'https://www.jkt48.com/events/mini-hs-jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'Special Video Call - Valentine Edition',
                'event_type' => 'video-call',
                'event_date' => '2025-02-14',
                'event_date_2' => '2025-02-15',
                'ticket_sale_datetime' => '2025-02-01 10:00:00',
                'purchase_link' => 'https://www.jkt48.com/videocall/valentine',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'Meet & Greet Mall Tour Surabaya',
                'event_type' => 'meet-greet',
                'event_date' => '2025-03-10',
                'event_date_2' => null,
                'ticket_sale_datetime' => null,
                'purchase_link' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_name' => 'Online Video Call - Special Session',
                'event_type' => 'video-call',
                'event_date' => '2025-03-25',
                'event_date_2' => '2025-03-26',
                'ticket_sale_datetime' => '2025-03-10 14:00:00',
                'purchase_link' => 'https://www.jkt48.com/videocall/march',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('meet_greet_events')->insert($events);
    }
}
