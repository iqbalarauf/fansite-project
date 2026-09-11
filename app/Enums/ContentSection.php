<?php

namespace App\Enums;

use App\Models\BlogPost;
use App\Models\NewsPost;
use App\Models\Post;

enum ContentSection: string
{
    case News = 'news';
    case Blog = 'blog';

    public function label(): string
    {
        return match ($this) {
            self::News => 'News',
            self::Blog => 'Blog',
        };
    }

    /**
     * @return class-string<Post>
     */
    public function model(): string
    {
        return match ($this) {
            self::News => NewsPost::class,
            self::Blog => BlogPost::class,
        };
    }

    public function publicIndexRoute(): string
    {
        return $this->value.'.index';
    }

    public function publicShowRoute(): string
    {
        return $this->value.'.show';
    }

    public function adminRoute(): string
    {
        return 'content.'.$this->value;
    }

    public function path(): string
    {
        return '/'.$this->value;
    }

    public function table(): string
    {
        return $this->value.'_posts';
    }
}
