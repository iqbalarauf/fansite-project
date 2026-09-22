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
        'reference_code',
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

    /**
     * Nama setlist dari normalisasi (relasi/pivot), fallback ke kolom teks mirror.
     */
    public function setlistName(): ?string
    {
        $name = $this->setlistCategory?->name;

        if (filled($name)) {
            return (string) $name;
        }

        return filled($this->setlist) ? (string) $this->setlist : null;
    }

    /**
     * Daftar nama unit song dari normalisasi (pivot), fallback ke kolom teks mirror.
     *
     * @return array<int, string>
     */
    public function unitSongNames(): array
    {
        $names = $this->unitSongCategories->pluck('name')->map('strval')->all();

        if ($names !== []) {
            return array_values($names);
        }

        return array_values(array_filter(array_map('trim', explode(';', (string) $this->unit_song))));
    }

    /**
     * Unit song dalam satu string mirip kolom teks (dipisah "; ").
     */
    public function unitSongString(): ?string
    {
        $names = $this->unitSongNames();

        return $names === [] ? null : implode('; ', $names);
    }
}
