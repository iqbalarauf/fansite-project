<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    /**
     * Append rendered HTML body to serialized model
     */
    protected $appends = ['body_html'];

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'category',
        'tags',
        'seo_title',
        'seo_description',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'tags' => 'array',
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
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $slug = Str::slug($post->title);
                $original = $slug;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $original.'-'.($i++);
                }
                $post->slug = $slug;
            }
        });
    }

    /**
     * Render the body text into safe HTML supporting basic Markdown-like
     * syntax for links, bold (**text**), italic (*text*), and line breaks.
     * This is intentionally simple to avoid adding heavy frontend deps.
     *
     * @return string
     */
    public function getBodyHtmlAttribute()
    {
        $text = $this->body ?? '';

        // Preferred: use league/commonmark + HTMLPurifier when available
        if (class_exists(\League\CommonMark\CommonMarkConverter::class) && class_exists(\HTMLPurifier::class)) {
            try {
                $converter = new \League\CommonMark\CommonMarkConverter([
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ]);

                if (method_exists($converter, 'convertToHtml')) {
                    $html = $converter->convertToHtml($text);
                } else {
                    $html = (string) $converter->convert($text);
                }

                $config = \HTMLPurifier_Config::createDefault();
                // allow a safe subset and keep iframes for common providers
                $config->set('HTML.SafeIframe', true);
                $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www.youtube.com/embed/|player.vimeo.com/video/)%');
                $purifier = new \HTMLPurifier($config);

                return $purifier->purify($html);
            } catch (\Throwable $e) {
                // fall through to simple renderer on any error
            }
        }

        // Fallback: simple, safe renderer (keeps earlier behaviour)
        $text = e($text);
        $text = preg_replace('/\[(.*?)\]\((https?:\/\/[^)\s]+)\)/i', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $text);
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
        $text = nl2br($text);

        return $text;
    }
}
