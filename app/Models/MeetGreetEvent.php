<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetGreetEvent extends Model
{
    protected $fillable = [
        'event_name',
        'event_type',
        'event_date',
        'event_date_2',
        'ticket_sale_datetime',
        'purchase_link',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_date_2' => 'date',
        'ticket_sale_datetime' => 'datetime',
    ];
}
