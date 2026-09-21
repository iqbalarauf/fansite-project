<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['platform', 'url', 'title', 'credit_account', 'sort_order'])]
class GalleryVideo extends Model
{
    use SoftDeletes;
}
