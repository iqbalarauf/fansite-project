<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleInertiaRequests
{
    /**
     * Minimal middleware replacement for Inertia's middleware.
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
