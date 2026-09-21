<?php

namespace App\Enums;

use App\Models\ConcertEvents;
use App\Models\LiveStreaming;
use App\Models\MeetGreetEvents;
use App\Models\ShowTeater;
use Illuminate\Database\Eloquent\Model;

enum MasterData: string
{
    case ShowTeater = 'show_teater';
    case LiveStreaming = 'live_streaming';
    case ConcertEvents = 'concert_events';
    case MeetGreetEvents = 'meet_greet_events';

    public function label(): string
    {
        return match ($this) {
            self::ShowTeater => 'Show Teater',
            self::LiveStreaming => 'Live Streaming',
            self::ConcertEvents => 'Concert & Event',
            self::MeetGreetEvents => 'Meet & Greet',
        };
    }

    /**
     * @return class-string<Model>
     */
    public function model(): string
    {
        return match ($this) {
            self::ShowTeater => ShowTeater::class,
            self::LiveStreaming => LiveStreaming::class,
            self::ConcertEvents => ConcertEvents::class,
            self::MeetGreetEvents => MeetGreetEvents::class,
        };
    }

    /**
     * Columns compared between the database and the spreadsheet (header row names).
     *
     * @return array<int, string>
     */
    public function columns(): array
    {
        return match ($this) {
            self::ShowTeater => [
                'show_id',
                'show_date',
                'setlist',
                'unit_song',
                'is_global_center',
                'is_us_center',
                'is_the_show_has_event',
                'additional_information',
                'is_scraped_data',
                'is_member_show',
            ],
            self::LiveStreaming => [
                'id',
                'live_id',
                'platform',
                'live_date',
                'duration',
                'additional_info',
            ],
            self::ConcertEvents => [
                'id',
                'event_name',
                'event_date',
                'location',
                'status',
                'purchase_link',
            ],
            self::MeetGreetEvents => [
                'id',
                'event_name',
                'event_type',
                'event_date',
                'event_date_2',
                'ticket_sale_datetime',
                'purchase_link',
                'location',
            ],
        };
    }

    /**
     * Column used to match a database record with a spreadsheet row.
     */
    public function keyColumn(): string
    {
        return match ($this) {
            self::ShowTeater => 'show_id',
            default => 'id',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case): string => $case->value, self::cases());
    }
}
