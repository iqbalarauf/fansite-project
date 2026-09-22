<?php

namespace Tests\Feature;

use App\Models\NewsPost;
use App\Support\HtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_profile_keeps_allowed_markup_and_strips_scripts(): void
    {
        $dirty = '<script>alert(1)</script><p onclick="steal()">Halo</p><strong>Tebal</strong><a href="javascript:alert(1)" target="_blank">Klik</a>';

        $clean = HtmlSanitizer::article($dirty);

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('<strong>Tebal</strong>', $clean);
        $this->assertStringContainsString('Halo', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_article_profile_strips_event_handlers_on_images(): void
    {
        $clean = HtmlSanitizer::article('<img src="https://example.com/a.jpg" onerror="alert(1)" alt="A">');

        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringContainsString('src="https://example.com/a.jpg"', $clean);
        $this->assertStringContainsString('alt="A"', $clean);
    }

    public function test_article_profile_rejects_iframe(): void
    {
        $clean = HtmlSanitizer::article('<iframe src="https://evil.test"></iframe><p>Aman</p>');

        $this->assertStringNotContainsString('<iframe', $clean);
        $this->assertStringContainsString('Aman', $clean);
    }

    public function test_embed_profile_allows_iframe_but_not_scripts_or_handlers(): void
    {
        $dirty = '<iframe src="https://www.youtube.com/embed/abc" onload="evil()" allowfullscreen></iframe><script>alert(1)</script><style>body{}</style>';

        $clean = HtmlSanitizer::embed($dirty);

        $this->assertStringContainsString('<iframe', $clean);
        $this->assertStringContainsString('https://www.youtube.com/embed/abc', $clean);
        $this->assertStringNotContainsString('onload', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('<style', $clean);
    }

    public function test_post_content_is_sanitized_on_save(): void
    {
        $post = NewsPost::query()->create([
            'title' => 'Berita',
            'slug' => 'berita',
            'status' => 'published',
            'content' => '<p>Sah</p><script>alert(1)</script><img src="x" onerror="alert(1)">',
        ]);

        $stored = $post->fresh()->getRawOriginal('content');

        $this->assertStringContainsString('<p>Sah</p>', $stored);
        $this->assertStringNotContainsString('<script', $stored);
        $this->assertStringNotContainsString('onerror', $stored);
    }

    public function test_embed_block_is_sanitized_when_rendered(): void
    {
        $html = Blade::render('<x-custom-page-block :block="$block" />', [
            'block' => [
                'type' => 'embed',
                'data' => ['html' => '<div onclick="evil()">Aman</div><script>alert(1)</script>'],
            ],
        ]);

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringContainsString('Aman', $html);
    }
}
