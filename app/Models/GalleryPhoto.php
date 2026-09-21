<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['photo', 'description', 'credit_photographer', 'sort_order'])]
class GalleryPhoto extends Model
{
    use SoftDeletes;
}
