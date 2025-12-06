<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        // Check for dangerous protocols
        $dangerousProtocols = ['javascript:', 'data:', 'vbscript:', 'file:', 'about:'];

        foreach ($dangerousProtocols as $protocol) {
            if (stripos($value, $protocol) === 0) {
                $fail("The {$attribute} contains an unsafe protocol.");
                return;
            }
        }

        // Validate URL format (must be valid URL or relative path)
        if (!filter_var($value, FILTER_VALIDATE_URL) && !str_starts_with($value, '/')) {
            $fail("The {$attribute} must be a valid URL.");
            return;
        }

        // Additional check for encoded javascript
        $decoded = urldecode($value);
        foreach ($dangerousProtocols as $protocol) {
            if (stripos($decoded, $protocol) !== false) {
                $fail("The {$attribute} contains encoded unsafe content.");
                return;
            }
        }
    }
}
