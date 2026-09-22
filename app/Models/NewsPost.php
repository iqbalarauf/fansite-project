<?php

namespace App\Models;

use App\Enums\ContentSection;

class NewsPost extends Post
{
    protected $table = 'posts';

    public static function sectionType(): string
    {
        return ContentSection::News->value;
    }

    public function section(): ContentSection
    {
        return ContentSection::News;
    }
}
