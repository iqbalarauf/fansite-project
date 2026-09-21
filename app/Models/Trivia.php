<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'description', 'image', 'sort_order'])]
class Trivia extends Model
{
    use SoftDeletes;

    protected $table = 'trivias';
}
