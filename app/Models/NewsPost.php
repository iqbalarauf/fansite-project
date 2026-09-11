<?php

namespace App\Models;

use App\Enums\ContentSection;

class NewsPost extends Post
{
    protected $table = 'news_posts';

    public function section(): ContentSection
    {
        return ContentSection::News;
    }
}
