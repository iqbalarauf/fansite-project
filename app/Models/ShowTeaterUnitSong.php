<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShowTeaterUnitSong extends Model
{
    protected $table = 'show_teater_unit_song';

    protected $fillable = [
        'show_id',
        'show_teater_categories_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];
}
