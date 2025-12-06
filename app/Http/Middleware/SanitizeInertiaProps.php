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
    protected function sanitizeProps(mixed $props): mixed
    {
        if (is_array($props)) {
            return array_map(function ($value) {
                return $this->sanitizeProps($value);
            }, $props);
        }

        if (is_string($props)) {
            return $this->sanitizeString($props);
        }

        return $props;
    }

    /**
     * Sanitize a string value.
     */
    protected function sanitizeString(string $value): string
    {
        // Remove potentially dangerous HTML/JavaScript
        $value = strip_tags($value, '<p><a><strong><em><ul><ol><li><br><h1><h2><h3><h4><h5><h6><img><span><div>');
        
        // Remove script tags and event handlers
        $value = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $value);
        $value = preg_replace('/\bon\w+\s*=\s*["\'].*?["\']/i', '', $value);
        
        // Remove javascript: protocol
        $value = preg_replace('/javascript:/i', '', $value);
        
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
            // Add keys that shouldn't be sanitized
            // Example: 'html_content', 'raw_data'
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
