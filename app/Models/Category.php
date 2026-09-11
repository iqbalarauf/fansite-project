<?php

namespace App\Models;

use App\Enums\ContentSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'type',
        'name',
        'slug',
    ];

    /**
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeOfType(Builder $query, ContentSection|string $type): Builder
    {
        return $query->where('type', $type instanceof ContentSection ? $type->value : $type);
    }
}
