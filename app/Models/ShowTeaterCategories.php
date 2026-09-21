<?php

namespace App\Models;

use Database\Factories\ShowTeaterCategoriesFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShowTeaterCategories extends Model
{
    /** @use HasFactory<ShowTeaterCategoriesFactory> */
    use HasFactory;

    public const TYPE_SETLIST = 'setlist';

    public const TYPE_UNIT_SONG = 'unit_song';

    protected $table = 'show_teater_categories';

    protected $fillable = [
        'setlist_id',
        'type',
        'name',
        'jp_name',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function setlist(): BelongsTo
    {
        return $this->belongsTo(self::class, 'setlist_id');
    }

    public function unitSongs(): HasMany
    {
        return $this->hasMany(self::class, 'setlist_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeSetlists(Builder $query): void
    {
        $query->where('type', self::TYPE_SETLIST);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeUnitSongs(Builder $query): void
    {
        $query->where('type', self::TYPE_UNIT_SONG);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
