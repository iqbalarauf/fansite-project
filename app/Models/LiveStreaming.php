<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStreaming extends Model
{
    use HasFactory;

    protected $table = 'live_streaming';

    protected $fillable = [
        'live_id',
        'platform',
        'live_date',
        'start_time',
        'end_time',
        'duration',
        'max_viewers',
        'comment_count',
        'gift_count',
        'total_gold',
        'youtube_url',
        'gifts',
        'top_senders',
        'additional_info',
    ];

    protected $casts = [
        'live_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'max_viewers' => 'integer',
        'comment_count' => 'integer',
        'gift_count' => 'integer',
        'total_gold' => 'integer',
        'gifts' => 'array',
        'top_senders' => 'array',
    ];
}
