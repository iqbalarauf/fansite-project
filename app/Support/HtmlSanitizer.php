<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Sanitizer HTML berbasis DOM (tanpa dependensi eksternal).
 *
 * - Profil `article`: untuk konten artikel (News/Blog) — tag rich-text editor.
 * - Profil `embed`: untuk blok "embed" page builder — lebih longgar (iframe, style).
 *
 * Membuang <script>/event handler/protokol berbahaya dan tag di luar whitelist.
 */
final class HtmlSanitizer
{
    public const PROFILE_ARTICLE = 'article';

    public const PROFILE_EMBED = 'embed';

    /**
     * Tag yang selalu dibuang beserta isinya.
     *
     * @var array<int, string>
     */
    private const DROP_TAGS = [
        'script', 'style', 'object', 'embed', 'link', 'meta', 'base', 'title',
        'form', 'input', 'textarea', 'select', 'option', 'button', 'noscript',
        'template', 'svg', 'math', 'frame', 'frameset', 'applet', 'body', 'html', 'head',
    ];

    /**
     * Atribut yang diizinkan untuk semua tag.
     *
     * @var array<int, string>
     */
    private const GLOBAL_ATTRS = ['class', 'title'];

    /**
     * @var array<string, array<int, string>>
     */
    private const ARTICLE_ATTRS = [
        'a' => ['href', 'target', 'rel', 'title'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'th' => ['colspan', 'rowspan', 'scope'],
        'td' => ['colspan', 'rowspan'],
        'ol' => ['start', 'type'],
        'blockquote' => ['cite'],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const EMBED_ATTRS = [
        'a' => ['href', 'target', 'rel', 'title'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'iframe' => ['src', 'width', 'height', 'allow', 'allowfullscreen', 'frameborder', 'loading', 'referrerpolicy', 'sandbox'],
        'video' => ['src', 'controls', 'width', 'height', 'poster', 'preload', 'loop', 'muted', 'playsinline'],
        'audio' => ['src', 'controls', 'preload', 'loop'],
        'source' => ['src', 'type', 'srcset', 'media'],
        'th' => ['colspan', 'rowspan', 'scope'],
        'td' => ['colspan', 'rowspan'],
        'ol' => ['start', 'type'],
        'blockquote' => ['cite'],
    ];

    /**
     * @var array<int, string>
     */
    private const ARTICLE_TAGS = [
        'p', 'br', 'span', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'del', 'ins',
        'sub', 'sup', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote',
        'pre', 'code', 'a', 'img', 'hr', 'figure', 'figcaption', 'table', 'thead',
        'tbody', 'tfoot', 'tr', 'th', 'td', 'div', 'mark',
    ];

    /**
     * @var array<int, string>
     */
    private const EMBED_TAGS = [
        'p', 'br', 'span', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'del', 'ins',
        'sub', 'sup', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote',
        'pre', 'code', 'a', 'img', 'hr', 'figure', 'figcaption', 'table', 'thead',
        'tbody', 'tfoot', 'tr', 'th', 'td', 'div', 'mark',
        'section', 'article', 'header', 'footer', 'nav', 'aside', 'details', 'summary',
        'small', 'time', 'label', 'iframe', 'video', 'audio', 'source', 'picture',
    ];

    public static function article(?string $html): string
    {
        return self::clean($html, self::PROFILE_ARTICLE);
    }

    public static function embed(?string $html): string
    {
        return self::clean($html, self::PROFILE_EMBED);
    }

    public static function clean(?string $html, string $profile): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        $isEmbed = $profile === self::PROFILE_EMBED;
        $allowedTags = array_flip($isEmbed ? self::EMBED_TAGS : self::ARTICLE_TAGS);
        $allowedAttrs = $isEmbed ? self::EMBED_ATTRS : self::ARTICLE_ATTRS;

        $document = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div data-sanitizer-root>'.$html.'</div>', LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $wrapper = $document->getElementsByTagName('div')->item(0);

        if (! $wrapper instanceof DOMElement) {
            return '';
        }

        self::cleanNode($wrapper, $allowedTags, $allowedAttrs, $isEmbed);

        $output = '';

        foreach ($wrapper->childNodes as $child) {
            $output .= (string) $document->saveHTML($child);
        }

        return trim($output);
    }

    /**
     * @param  array<string, int>  $allowedTags
     * @param  array<string, array<int, string>>  $allowedAttrs
     */
    private static function cleanNode(DOMNode $node, array $allowedTags, array $allowedAttrs, bool $isEmbed): void
    {
        for ($child = $node->firstChild; $child !== null; $child = $next) {
            $next = $child->nextSibling;

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::DROP_TAGS, true)) {
                $node->removeChild($child);

                continue;
            }

            // Bersihkan anak terlebih dahulu (agar isi tag yang nanti di-unwrap tetap aman).
            self::cleanNode($child, $allowedTags, $allowedAttrs, $isEmbed);

            if (! isset($allowedTags[$tag])) {
                while ($child->firstChild !== null) {
                    $node->insertBefore($child->firstChild, $child);
                }

                $node->removeChild($child);

                continue;
            }

            self::cleanAttributes($child, $tag, $allowedAttrs[$tag] ?? [], $isEmbed);
        }
    }

    /**
     * @param  array<int, string>  $allowedAttrs
     */
    private static function cleanAttributes(DOMElement $element, string $tag, array $allowedAttrs, bool $isEmbed): void
    {
        for ($index = $element->attributes->length - 1; $index >= 0; $index--) {
            $attribute = $element->attributes->item($index);

            if ($attribute === null) {
                continue;
            }

            $name = strtolower($attribute->nodeName);
            $allowed = in_array($name, self::GLOBAL_ATTRS, true) || in_array($name, $allowedAttrs, true);

            if ($isEmbed && (str_starts_with($name, 'data-') || $name === 'style')) {
                $allowed = true;
            }

            if (str_starts_with($name, 'on')) {
                $allowed = false;
            }

            if (! $allowed) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            $value = (string) $attribute->value;

            if ($name === 'style') {
                if (preg_match('/expression\s*\(|javascript:|vbscript:|url\s*\(\s*[\'"]?\s*(?:javascript|vbscript|data):/i', $value)) {
                    $element->removeAttribute($attribute->nodeName);
                }

                continue;
            }

            if (in_array($name, ['href', 'src', 'srcset', 'poster'], true) && ! self::isSafeUrl($value)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }

        if ($tag === 'a' && strtolower($element->getAttribute('target')) === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        $normalized = preg_replace('/[\x00-\x20]+/', '', strtolower(trim($url))) ?? '';

        if ($normalized === '') {
            return true;
        }

        return ! str_starts_with($normalized, 'javascript:')
            && ! str_starts_with($normalized, 'vbscript:')
            && ! str_starts_with($normalized, 'data:');
    }
}
