<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['photo', 'description', 'credit_photographer'])]
class GalleryPhoto extends Model
{
    //
}
