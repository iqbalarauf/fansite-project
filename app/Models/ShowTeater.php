<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShowTeater extends Model
{
    protected $table = 'show_teater';
    protected $primaryKey = 'show_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'show_id',
        'show_date',
        'setlist',
        'unit_song',
        'is_global_center',
        'is_us_center',
        'is_the_show_has_event',
        'additional_information'
    ];
}
