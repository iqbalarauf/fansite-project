<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcertEvent extends Model
{
    protected $fillable = [
        'event_name',
        'event_date',
        'location',
        'status',
        'purchase_link',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];
}
