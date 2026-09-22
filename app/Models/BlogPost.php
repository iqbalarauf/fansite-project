<?php

namespace App\Models;

use App\Enums\ContentSection;

class BlogPost extends Post
{
    protected $table = 'posts';

    public static function sectionType(): string
    {
        return ContentSection::Blog->value;
    }

    public function section(): ContentSection
    {
        return ContentSection::Blog;
    }
}
