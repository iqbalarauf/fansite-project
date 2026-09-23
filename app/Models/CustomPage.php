<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'slug', 'status', 'display_mode', 'background_color', 'title_alignment', 'hero_enabled', 'hero_image', 'blocks'])]
class CustomPage extends Model
{
    use HasAuditColumns;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'hero_enabled' => 'boolean',
        ];
    }
}
