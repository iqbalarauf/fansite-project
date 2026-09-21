<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class RichTextEditorTest extends TestCase
{
    public function test_toolbar_renders_icon_buttons_instead_of_text_labels(): void
    {
        $html = Blade::render('<x-rich-text-editor name="content" />');

        $commands = [
            'bold', 'italic', 'underline', 'strike', 'h2', 'h3',
            'bulletList', 'orderedList', 'blockquote', 'codeBlock',
            'link', 'image', 'undo', 'redo', 'clear',
        ];

        foreach ($commands as $command) {
            $this->assertStringContainsString('data-rich-text-command="'.$command.'"', $html);
        }

        // Satu ikon SVG per tombol.
        $this->assertSame(count($commands), substr_count($html, '<svg'));

        // Label berbasis karakter tidak lagi ditampilkan.
        foreach (['>Daftar<', '>Kutipan<', '>Kode<', '>Tautan<', '>Gambar<', '>Bersihkan<'] as $label) {
            $this->assertStringNotContainsString($label, $html);
        }

        // Gambar diunggah lewat input berkas (bukan prompt link).
        $this->assertStringContainsString('data-rich-text-image-input', $html);
        $this->assertStringContainsString('data-image-upload-url=', $html);
        $this->assertStringContainsString('content/editor/image', $html);
    }
}
