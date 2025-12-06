<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveStreaming extends Model
{
    protected $table = 'live_streaming';

    protected $fillable = [
        'platform',
        'live_date',
        'duration',
        'additional_info',
    ];

    protected $casts = [
        'live_date' => 'date',
    ];
}
