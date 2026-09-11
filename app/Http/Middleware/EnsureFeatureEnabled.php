<?php

namespace App\Http\Middleware;

use App\Support\SettingBag;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    /**
     * Reject the request unless at least one of the given content features is enabled.
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        foreach ($features as $feature) {
            if (SettingBag::featureEnabled($feature)) {
                return $next($request);
            }
        }

        abort(404);
    }
}
