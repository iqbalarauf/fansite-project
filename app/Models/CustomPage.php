<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'slug', 'status', 'display_mode', 'background_color', 'title_alignment', 'blocks'])]
class CustomPage extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
        ];
    }
}
