<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventViewOnlyWrites
{
    /**
     * All View-Only users can view every page they have access to, but every
     * mutating (non read-only) request must be rejected server-side.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isViewOnly() && ! $request->isMethodCacheable()) {
            abort(403, 'Akun View-Only tidak dapat melakukan perubahan data.');
        }

        return $next($request);
    }
}
