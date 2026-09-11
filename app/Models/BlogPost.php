<?php

namespace App\Models;

use App\Enums\ContentSection;

class BlogPost extends Post
{
    protected $table = 'blog_posts';

    public function section(): ContentSection
    {
        return ContentSection::Blog;
    }
}
