<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'image'])]
class Trivia extends Model
{
    protected $table = 'trivias';
}
