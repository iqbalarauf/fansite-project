<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'hero_image',
        'body',
        'has_cta_section',
        'cta_bg_image',
        'cta_title',
        'cta_description',
        'cta_button_text',
        'cta_button_url',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'has_cta_section' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function booted()
    {
        static::creating(function ($page) {
            if (empty($page->slug)) {
                $slug = Str::slug($page->title);
                $original = $slug;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $original.'-'.($i++);
                }
                $page->slug = $slug;
            }
        });
    }
}
