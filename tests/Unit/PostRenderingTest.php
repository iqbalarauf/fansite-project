<?php

namespace Tests\Unit;

use App\Models\Post;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostRenderingTest extends TestCase
{
    /** @test */
    public function it_renders_markdown_and_sanitizes_script_tags()
    {
        $body = "This is **bold** and *italic*.\nVisit [Google](https://google.com). <script>alert('x')</script>";
        $post = Post::factory()->make(['body' => $body]);

        $html = $post->body_html;

        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<em>italic</em>', $html);
        $this->assertStringContainsString('<a href="https://google.com"', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }
}
