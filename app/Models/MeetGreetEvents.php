<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetGreetEvents extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'meet_greet_events';

    protected $fillable = [
        'event_name',
        'event_type',
        'event_date',
        'event_date_2',
        'ticket_sale_datetime',
        'purchase_link',
        'location',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_date_2' => 'date',
        'ticket_sale_datetime' => 'datetime',
    ];
}
