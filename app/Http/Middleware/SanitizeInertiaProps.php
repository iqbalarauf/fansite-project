<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInertiaProps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process Inertia responses
        if (!$this->isInertiaResponse($response)) {
            return $response;
        }

        // Get the original content
        $content = $response->getContent();

        // Decode the JSON content
        $data = json_decode($content, true);

        if (isset($data['props'])) {
            // Sanitize the props recursively
            $data['props'] = $this->sanitizeProps($data['props']);

            // Re-encode and set the content
            $response->setContent(json_encode($data));
        }

        return $response;
    }

    /**
     * Check if the response is an Inertia response.
     */
    protected function isInertiaResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type');
        $xInertia = $response->headers->get('X-Inertia');

        return $xInertia === 'true' ||
               (str_contains($contentType ?? '', 'application/json') &&
                request()->header('X-Inertia'));
    }

    /**
     * Recursively sanitize props.
     */
    protected function sanitizeProps(mixed $props, string $key = ''): mixed
    {
        if (is_array($props)) {
            $result = [];
            foreach ($props as $k => $value) {
                // Skip whitelisted keys
                if ($this->isWhitelisted($k)) {
                    $result[$k] = $value;
                } else {
                    $result[$k] = $this->sanitizeProps($value, $k);
                }
            }
            return $result;
        }

        if (is_string($props)) {
            return $this->sanitizeString($props, $key);
        }

        return $props;
    }

    /**
     * Sanitize a string value.
     */
    protected function sanitizeString(string $value, string $key = ''): string
    {
        // Validate and sanitize URLs
        if (str_contains($key, 'url') || str_contains($key, 'link') || str_contains($key, 'href')) {
            return $this->sanitizeUrl($value);
        }

        // For description fields, preserve some formatting but remove dangerous content
        if (str_contains($key, 'description') || str_contains($key, 'content')) {
            return $this->sanitizeHtmlContent($value);
        }

        // Default: remove all HTML tags
        $value = strip_tags($value);

        // Remove javascript: protocol
        $value = preg_replace('/javascript:/i', '', $value);

        // Trim whitespace
        $value = trim($value);

        return $value;
    }

    /**
     * Sanitize URL fields.
     */
    protected function sanitizeUrl(string $url): string
    {
        $url = trim($url);

        // Remove javascript: and data: protocols
        $url = preg_replace('/^(javascript|data|vbscript):/i', '', $url);

        // Validate URL format
        if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL) && !str_starts_with($url, '/')) {
            return '';
        }

        return $url;
    }

    /**
     * Sanitize HTML content (for descriptions, etc).
     */
    protected function sanitizeHtmlContent(string $value): string
    {
        // Allow safe HTML tags
        $value = strip_tags($value, '<p><a><strong><em><ul><ol><li><br><h1><h2><h3><h4><h5><h6><span><div>');

        // Remove script tags and event handlers
        $value = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $value);
        $value = preg_replace('/\bon\w+\s*=\s*["\'].*?["\']/i', '', $value);

        // Remove dangerous attributes from links
        $value = preg_replace('/<a\s+([^>]*\s+)?href\s*=\s*["\']javascript:/i', '<a $1href="', $value);

        // Trim whitespace
        $value = trim($value);

        return $value;
    }

    /**
     * Get list of props keys that should not be sanitized.
     * Override this method in your middleware if needed.
     */
    protected function getWhitelistedKeys(): array
    {
        return [
            // Add keys that shouldn't be sanitized (use with caution!)
            // Example: 'html_content', 'raw_data'
            'errors', // Laravel validation errors
            'errorBags', // Inertia error bags
        ];
    }

    /**
     * Check if a key is whitelisted.
     */
    protected function isWhitelisted(string $key): bool
    {
        return in_array($key, $this->getWhitelistedKeys());
    }
}
