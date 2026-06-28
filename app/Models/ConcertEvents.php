<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConcertEvents extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'concert_events';

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
