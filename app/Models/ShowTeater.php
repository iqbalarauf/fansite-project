<?php

namespace App\Models;

use Database\Factories\ShowTeaterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShowTeater extends Model
{
    /** @use HasFactory<ShowTeaterFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'show_teater';

    protected $primaryKey = 'show_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'show_id',
        'show_date',
        'setlist',
        'setlist_id',
        'unit_song',
        'is_global_center',
        'is_us_center',
        'is_the_show_has_event',
        'additional_information',
        'is_scraped_data',
        'is_member_show',
        'last_fetch_at',
    ];

    public function setlistCategory(): BelongsTo
    {
        return $this->belongsTo(ShowTeaterCategories::class, 'setlist_id');
    }

    public function unitSongCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            ShowTeaterCategories::class,
            'show_teater_unit_song',
            'show_id',
            'show_teater_categories_id',
        )->withPivot('position')->withTimestamps()->orderByPivot('position');
    }
}
