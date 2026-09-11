<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'slug',
    'description',
    'cover',
    'file_path',
    'original_name',
    'is_main',
    'views',
    'downloads',
])]
class Magazine extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'views' => 'integer',
            'downloads' => 'integer',
        ];
    }

    /**
     * @param  Builder<Magazine>  $query
     * @return Builder<Magazine>
     */
    public function scopeMain(Builder $query): Builder
    {
        return $query->where('is_main', true);
    }
}
