<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'sidebar_name',
        'hero_path',
        'login_image_path',
        'app_logo_path',
    ];
}
